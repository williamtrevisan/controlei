<?php

namespace App\Actions;

use App\DataTransferObjects\TransactionData;
use App\Models\Account;
use App\Models\IncomeSource;
use Banklink\Entities\Card;
use Banklink\Entities\CardStatement;
use Banklink\Entities\Transaction;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class GetAllBankTransactions
{
    public function __construct(
        private readonly GetAllIncomeSource $getAllIncomeSource,
        private readonly GetAllExpenses $getAllExpenses,
        private readonly FindOrCreateAccount $findOrCreateAccount,
        private readonly FindOrCreateCard $findOrCreateCard
    ) {
    }

    public function execute(string $token): LazyCollection
    {
        return DB::transaction(function () use ($token) {
            $bankAccount = banklink()
                ->authenticate($token)
                ->account();

            $account = $this->findOrCreateAccount->execute($bankAccount);
            $incomeSources = $this->getAllIncomeSource->execute();
            $expenses = $this->getAllExpenses->execute();
            $bankCards = $bankAccount->cards()->all();

            return $bankAccount->transactions()
                ->between(now()->startOfYear(), now())
                ->map($this->toTransactionData($account, $incomeSources, $expenses, $bankCards->first()->dueDay()))
                ->merge(
                    $bankCards
                        ->flatMap($this->processCardTransactions($account, $incomeSources, $expenses, $bankCards))
                )
                ->pipe(fn (Collection $transactions) => $this->connectInstallments($transactions))
                ->lazy();
        });
    }

    private function toTransactionData(Account $account, Collection $incomeSources, Collection $expenses, int $dueDay): Closure
    {
        return function (Transaction $transaction) use ($account, $incomeSources, $expenses, $dueDay): TransactionData {
            return TransactionData::from(
                transaction: $transaction,
                accountId: $account->id,
                incomeSourceId: $transaction->direction()->isInflow()
                    ? $incomeSources
                        ->first(fn (IncomeSource $incomeSource): bool => str($transaction->description())->isMatch($incomeSource->matcher_regex))
                        ?->id
                    : null,
                expenseId: $transaction->direction()->isOutflow()
                    ? $expenses
                        ->first(fn ($expense): bool => str($transaction->description())->isMatch($expense->matcher_regex))
                        ?->id
                    : null,
                statementPeriod: $this->generateStatementPeriod($transaction, $dueDay),
            );
        };
    }

    private function generateStatementPeriod(Transaction $transaction, int $dueDay): string
    {
        $bank = config('banklink.bank');

        $dueDate = $transaction->date()->clone()
            ->addMonth()
            ->setDay($dueDay);

        $closingDate = $dueDate->clone()
            ->subDays(config()->integer("banklink.banks.$bank.closing_due_interval_days"));

        if ($transaction->date()->greaterThanOrEqualTo($closingDate)) {
            return $dueDate->clone()
                ->addMonth()
                ->format('Y-m');
        }

        return $dueDate->format('Y-m');
    }

    private function processCardTransactions(Account $account, Collection $incomeSources, Collection $expenses, Collection $bankCards): Closure
    {
        return function (Card $bankCard) use ($account, $incomeSources, $expenses, $bankCards): Collection {
            return $bankCard->statements()
                ->all()
                ->flatMap(function (CardStatement $statement) use ($account, $incomeSources, $expenses, $bankCards) {
                    return $statement->holders()
                        ->flatMap(function ($holder) use ($account, $incomeSources, $expenses, $bankCards) {
                            $card = $this->findOrCreateCard
                                ->execute(accountId: $account->id, holder: $holder, cards: $bankCards);

                            return $holder->transactions()
                                ->map(function (Transaction $transaction) use ($account, $card, $incomeSources, $expenses): TransactionData {
                                    return TransactionData::from(
                                        transaction: $transaction,
                                        accountId: $account->id,
                                        cardId: $card->id,
                                        incomeSourceId: $transaction->direction()->isInflow()
                                            ? $incomeSources
                                                ->first(fn (IncomeSource $incomeSource): bool => str($transaction->description())->isMatch($incomeSource->matcher_regex))
                                                ?->id
                                            : null,
                                        expenseId: $transaction->direction()->isOutflow()
                                            ? $expenses
                                                ->first(fn ($expense): bool => str($transaction->description())->isMatch($expense->matcher_regex))
                                                ?->id
                                            : null,
                                        statementPeriod: $transaction->statementPeriod(),
                                    );
                                });
                        });
                });
        };
    }

    private function connectInstallments(Collection $transactions): Collection
    {
        $transactions
            ->filter(fn (TransactionData $transaction) => $transaction->totalInstallments > 1)
            ->groupBy(fn (TransactionData $transaction) => $this->getInstallmentSignature($transaction))
            ->each(function (Collection $group) {
                if ($group->count() <= 1) {
                    return;
                };

                $parent = $group
                    ->firstWhere('currentInstallment', 1);
                if (! $parent) {
                    $parent = $group->sortBy('currentInstallment')->first();
                }

                $group
                    ->reject(fn (TransactionData $transaction) => $transaction->id === $parent->id)
                    ->each(function (TransactionData $transaction) use ($parent) {
                        $transaction->parentTransactionId = $parent->id;
                    });
            });

        return $transactions;
    }

    private function getInstallmentSignature(TransactionData $transaction): string
    {
        return hash('sha256', implode('|', [
            $transaction->accountId,
            $transaction->cardId,
            $this->description($transaction->description),
            $transaction->amount,
            $transaction->totalInstallments,
        ]));
    }

    private function description(string $description): string
    {
        return str($description)
            ->limit(15, '')
            ->value();
    }
}
