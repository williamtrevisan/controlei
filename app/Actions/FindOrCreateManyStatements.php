<?php

namespace App\Actions;

use App\DataTransferObjects\StatementData;
use App\Enums\StatementStatus;
use App\Models\Card;
use App\Models\Statement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

readonly class FindOrCreateManyStatements
{
    public function __construct(
        private FindManyStatementsByCardIdAndPeriod $findManyStatementsByCardIdAndPeriod,
        private CreateManyStatements $createManyStatements,
    ) {
    }

    public function execute(Card $card, Carbon $transactionDate, int $totalInstallments = 1): Statement
    {
        $startDate = Carbon::createFromFormat('Y-m', ($transactionPeriod = $this->transactionPeriod($card, $transactionDate)));

        $lastInstallmentPeriod = Carbon::createFromFormat('Y-m', $transactionPeriod)
            ->addMonths($totalInstallments - 1)
            ->format('Y-m');

        $endDate = Carbon::createFromFormat('Y-m', $lastInstallmentPeriod);

        $statements = collect()
            ->range(0, ($startDate->diffInMonths($endDate) + 1) - 1)
            ->map(function (int $month) use ($card, $startDate): StatementData {
                $periodDate = $startDate
                    ->clone()
                    ->addMonths($month);
                $dueDate = $periodDate
                    ->clone()
                    ->setDay($card->due_day);
                $closingDate = $dueDate
                    ->clone()
                    ->subDays(config()->integer("banklink.banks.{$card->account->bank->value}.closing_due_interval_days"));

                return new StatementData(
                    accountId: $card->account->id,
                    cardId: $card->id,
                    parentStatementId: null,
                    period: $periodDate->format('Y-m'),
                    closingDate: $closingDate,
                    dueDate: $dueDate,
                    status: StatementStatus::Open,
                    amount: 0,
                );
            });

        $existingStatements = $this->findManyStatementsByCardIdAndPeriod->execute($statements);

        $statements
            ->reject(function (StatementData $data) use ($existingStatements): bool {
                return $existingStatements->contains(function (Statement $statement) use ($data): bool {
                    return $statement->card_id === $data->cardId
                        && $statement->period->value() === $data->period;
                });
            })
            ->pipe(fn (Collection $statements) => $this->createManyStatements->execute($statements));

        return $existingStatements->firstWhere('period', $transactionPeriod)
            ?? Statement::query()
                ->where('card_id', $card->id)
                ->where('period', $transactionPeriod)
                ->first();
    }

    private function transactionPeriod(Card $card, Carbon $transactionDate): string
    {
        $bank = $card->account->bank->value;
        $closingDueIntervalDays = config()->integer("banklink.banks.{$bank}.closing_due_interval_days");

        $currentDueDate = $transactionDate->clone()
            ->setDay($card->due_day);

        $closingDate = $currentDueDate->clone()
            ->subDays($closingDueIntervalDays);

        if ($transactionDate->greaterThanOrEqualTo($closingDate)) {
            $currentDueDate->addMonth();
        }

        return $currentDueDate->format('Y-m');
    }
}
