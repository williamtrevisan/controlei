<?php

namespace App\Repositories\Contracts;

use App\Enums\AccountBank;
use App\Enums\AccountType;
use App\Models\Account;

interface AccountRepository
{
    public function findOrCreateByBank(
        AccountType $type, 
        AccountBank $bank, 
        string $agency, 
        string $account, 
        string $accountDigit
    ): Account;
}