<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\Card;
use App\Models\Statement;
use App\Repositories\Contracts\StatementRepository;
use Illuminate\Support\Carbon;

class GetOrCreateStatementForTransaction
{
    public function __construct(
        private readonly StatementRepository $statementRepository,
    ) {
    }

    public function execute(Card $card, Carbon $transactionDate): Statement
    {
        $period = $this->calculateStatementPeriod($card, $transactionDate);
        return $this->statementRepository->findOrCreateForCard($card, $period);
    }

    public function executeForAccount(Account $account, Carbon $transactionDate): Statement
    {
        // If the account has cards, use the first card's statement period logic
        // This ensures account and card transactions share the same statement
        $card = $account->cards()->first();
        
        if ($card) {
            // Use card's statement period calculation
            $period = $this->calculateStatementPeriod($card, $transactionDate);
            return $this->statementRepository->findOrCreateForCard($card, $period);
        }
        
        // If no cards exist, create an account-level statement
        $period = $transactionDate->format('Y-m');
        return $this->statementRepository->findOrCreateForAccount($account, $period);
    }

    private function calculateStatementPeriod(Card $card, Carbon $date): string
    {
        $closingDay = $card->closing_day;

        // If the transaction date is after the closing day, it belongs to the next statement period
        if ($date->day > $closingDay) {
            return $date->copy()->addMonth()->format('Y-m');
        }

        return $date->format('Y-m');
    }
}
