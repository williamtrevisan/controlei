<?php

namespace App\Actions;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepository;

class GetUserAccount
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
    ) {
    }

    public function execute(): ?Account
    {
        return $this->accountRepository->first();
    }
}

