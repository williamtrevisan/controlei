<?php

namespace App\Actions;

use App\DataTransferObjects\TransactionMemberData;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\InviteRepository;
use App\Repositories\Contracts\TransactionMemberRepository;
use App\Repositories\Contracts\TransactionRepository;
use Illuminate\Support\Collection;

final readonly class ShareManyTransactionsWithUser
{
    public function __construct(
        private InviteRepository $inviteRepository,
        private TransactionRepository $transactionRepository,
        private TransactionMemberRepository $transactionMemberRepository
    ) {}

    public function execute(Collection $transactions, User $member): void
    {
        $user = auth()->user();
        if (! $this->areUsersConnected($user, $member)) {
            throw new \Exception('Users are not connected. Accept an invite first.');
        }

        $transactions
            ->flatMap(fn (Transaction $transaction) => $this->transactionRepository->getAllInstallments($transaction))
            ->unique('id')
            ->reject(function (Transaction $installment) use ($member): bool {
                return $this->transactionMemberRepository
                    ->findAlreadyShared(collect([$installment->id]), $member->id)
                    ->isNotEmpty();
            })
            ->map(function (Transaction $installment) use ($user, $member): TransactionMemberData {
                return TransactionMemberData::from(
                    transactionId: $installment->id,
                    ownerId: $user->id,
                    memberId: $member->id,
                );
            })
            ->pipe(fn (Collection $memberships) => $this->transactionMemberRepository->createMany($memberships));
    }

    private function areUsersConnected(User $owner, User $member): bool
    {
        return $this->inviteRepository
            ->findConnectedUsers($owner->id)
            ->contains('id', $member->id);
    }
}

