<?php

namespace App\Actions;

use App\Enums\AccountBank;
use App\Enums\AccountType;
use App\Models\Account;
use App\Repositories\Contracts\AccountRepository;
use Banklink\Entities\Account as BankAccount;

class FindOrCreateAccount
{
    public function __construct(
        private readonly AccountRepository $accountRepository
    ) {
    }

    public function execute(BankAccount $account): Account
    {
        $bank = AccountBank::from($account->bank());

        return $this->accountRepository->findOrCreateByBank(
            type: AccountType::Checking,
            bank: $bank,
            agency: $account->agency(),
            account: $account->number(),
            accountDigit: $account->digit(),
        );
    }
}
