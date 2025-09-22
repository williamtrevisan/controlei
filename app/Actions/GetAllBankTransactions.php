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
            $bankCards = $bankAccount->cards()->all();

            return $bankAccount->transactions()
                ->between(now()->startOfYear(), now())
                ->map($this->toTransactionData($account, $incomeSources, $bankCards->first()->dueDay()))
                ->merge(
                    $bankCards
                        ->flatMap($this->processCardTransactions($account, $incomeSources, $bankCards))
                )
                ->lazy();
        });
    }

    private function toTransactionData(Account $account, Collection $incomeSources, int $dueDay): Closure
    {
        return function (Transaction $transaction) use ($account, $incomeSources, $dueDay): TransactionData {
            return TransactionData::from(
                transaction: $transaction,
                accountId: $account->id,
                incomeSourceId: $transaction->direction()->isInflow()
                    ? $incomeSources
                        ->first(fn (IncomeSource $incomeSource): bool => str($transaction->description())->isMatch($incomeSource->matcher_regex))
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

    private function processCardTransactions(Account $account, Collection $incomeSources, Collection $bankCards): Closure
    {
        return function (Card $bankCard) use ($account, $incomeSources, $bankCards): Collection {
            return $bankCard->statements()
                ->all()
                ->flatMap(function (CardStatement $statement) use ($account, $incomeSources, $bankCards) {
                    return $statement->holders()
                        ->flatMap(function ($holder) use ($account, $incomeSources, $bankCards) {
                            $card = $this->findOrCreateCard
                                ->execute(accountId: $account->id, holder: $holder, cards: $bankCards);

                            return $holder->transactions()
                                ->map(function (Transaction $transaction) use ($account, $card, $incomeSources): TransactionData {
                                    return TransactionData::from(
                                        transaction: $transaction,
                                        accountId: $account->id,
                                        cardId: $card->id,
                                        incomeSourceId: $transaction->direction()->isInflow()
                                            ? $incomeSources
                                                ->first(fn (IncomeSource $incomeSource): bool => str($transaction->description())->isMatch($incomeSource->matcher_regex))
                                                ?->id
                                            : null,
                                        statementPeriod: $transaction->statementPeriod(),
                                    );
                                });
                        });
                });
        };
    }
}
