<?php

namespace App\Services\Contracts;

use App\Models\Transaction;

interface InstallmentsGenerator
{
    public function generate(Transaction $transaction): void;
}
