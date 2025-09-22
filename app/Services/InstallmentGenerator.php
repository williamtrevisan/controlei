<?php

namespace App\Services;

use App\Models\Transaction;

class InstallmentGenerator implements Contracts\InstallmentsGenerator
{
    public function generate(Transaction $transaction): void
    {
        if (is_null($transaction->current_installment)) {
            return;
        }

        if ($transaction->remaining_installments === 0) {
            return;
        }

        for ($i = 1; $i <= $transaction->remaining_installments; $i++) {
            $currentStatement = $transaction->current_installment + $i;
            $statementPeriod = $transaction->statement_period->advance($i);

            $installmentAttributes = [
                'account_id' => $transaction->account_id,
                'card_id' => $transaction->card_id,
                'income_source_id' => $transaction->income_source_id,
                'date' => $transaction->date->copy()->addMonths($i),
                'description' => $this->updateDescriptionInstallment(
                    $transaction->description,
                    $currentStatement,
                    $transaction->total_installments,
                ),
                'amount' => $transaction->amount,
                'direction' => $transaction->direction,
                'kind' => $transaction->kind,
                'payment_method' => $transaction->payment_method,
                'current_installment' => $currentStatement,
                'total_installments' => $transaction->total_installments,
                'statement_period' => $statementPeriod,
            ];

            if (Transaction::wouldBeDuplicate($installmentAttributes)) {
                continue;
            }

            Transaction::create($installmentAttributes);
        }
    }

    private function updateDescriptionInstallment(string $description, int $currentInstallment, int $totalInstallments): string
    {
        $pattern = '/(\d{1,2}\/\d{1,2})$/';

        if (preg_match($pattern, $description)) {
            return preg_replace($pattern, "{$currentInstallment}/{$totalInstallments}", $description);
        }

        return $description . " {$currentInstallment}/{$totalInstallments}";
    }
}
