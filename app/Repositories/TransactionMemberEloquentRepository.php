<?php

namespace App\Repositories;

use App\DataTransferObjects\TransactionMemberData;
use App\Models\TransactionMember;
use App\Repositories\Contracts\TransactionMemberRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TransactionMemberEloquentRepository implements TransactionMemberRepository
{
    public function __construct(
        protected readonly Model $model,
    ) {}

    protected function builder(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @param Collection $transactionIds
     * @param int $memberId
     * @return Collection<int, TransactionMember>
     */
    public function findAlreadyShared(Collection $transactionIds, string $memberId): Collection
    {
        return $this->builder()
            ->whereIn('transaction_id', $transactionIds)
            ->where('member_id', $memberId)
            ->get();
    }

    /**
     * @param Collection<int, TransactionMemberData> $memberships
     * @return bool
     */
    public function createMany(Collection $memberships): bool
    {
        return $this->builder()
            ->insert($memberships->toArray());
    }
}
