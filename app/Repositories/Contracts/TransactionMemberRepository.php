<?php

namespace App\Repositories\Contracts;

use App\Models\TransactionMember;
use Illuminate\Support\Collection;

interface TransactionMemberRepository
{
    /**
     * @param Collection $transactionIds
     * @param int $memberId
     * @return Collection<int, TransactionMember>
     */
    public function findAlreadyShared(Collection $transactionIds, int $memberId): Collection;

    /**
     * @param Collection $memberships
     * @return bool
     */
    public function createMany(Collection $memberships): bool;
}