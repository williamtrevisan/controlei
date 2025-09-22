<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    public function creating(Transaction $transaction): void
    {
        if (Transaction::where('hash', $hash = $transaction->hash())->exists()) {
            return;
        }

        $transaction->hash = $hash;
    }
}
