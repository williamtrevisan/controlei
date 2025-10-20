<?php

namespace App\Repositories;

use App\Enums\AccountBank;
use App\Enums\AccountType;
use App\Models\Account;
use App\Repositories\Contracts\AccountRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AccountEloquentRepository implements AccountRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {
    }

    protected function builder(): Builder
    {
        return $this->model
            ->newQuery()
            ->where('user_id', auth()->id());
    }

    public function findOrCreateByBank(
        AccountType $type,
        AccountBank $bank,
        string $agency,
        string $account,
        string $accountDigit
    ): Account {
        return $this->builder()
            ->firstOrCreate([
                'user_id' => auth()->id(),
                'bank' => $bank->value,
                'agency' => $agency,
                'account' => $account,
                'account_digit' => $accountDigit,
            ], ['type' => $type]);
    }

    public function first(): ?Account
    {
        return $this->builder()
            ->where('user_id', auth()->id())
            ->first();
    }
}
