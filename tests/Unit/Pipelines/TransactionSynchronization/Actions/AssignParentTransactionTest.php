<?php

use App\DataTransferObjects\SynchronizationData;
use App\Models\Account;
use App\Models\User;
use App\Pipelines\TransactionSynchronization\Actions\AssignParentTransaction;
use Banklink\Contracts\Bank;
use Tests\Support\Factories\TransactionDataFactory;

it('assigns parent transaction id to child installments', function () {
    $this->actingAs(User::factory()->create());

    $account = Account::factory()->create();

    $transactions = factory(TransactionDataFactory::class)
        ->sequence([
            ['currentInstallment' => 1, 'totalInstallments' => 3],
            ['currentInstallment' => 2, 'totalInstallments' => 3],
            ['currentInstallment' => 3, 'totalInstallments' => 3],
        ])
        ->create(count: 3, attributes: [
            'accountId' => $account->id,
            'description' => 'PURCHASE STORE',
            'amount' => 15000,
        ]);

    $data = app()->make(AssignParentTransaction::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect($transactions)->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    $parent = $data->transactions->firstWhere('currentInstallment', 1);

    expect($data)
        ->transactions->first()->parentTransactionId->toBeNull()
        ->and($data->transactions->get(1)->parentTransactionId)->toBe($parent->id)
        ->and($data->transactions->get(2)->parentTransactionId)->toBe($parent->id);
});

it('uses lowest installment as parent when first installment is missing', function () {
    $this->actingAs(User::factory()->create());

    $account = Account::factory()->create();

    $transactions = factory(TransactionDataFactory::class)
        ->sequence([
            ['currentInstallment' => 2, 'totalInstallments' => 3],
            ['currentInstallment' => 3, 'totalInstallments' => 3],
        ])
        ->create(count: 2, attributes: [
            'accountId' => $account->id,
            'description' => 'PURCHASE STORE',
            'amount' => 15000,
        ]);

    $data = app()->make(AssignParentTransaction::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect($transactions)->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    $parent = $data->transactions->firstWhere('currentInstallment', 2);

    expect($data)
        ->transactions->first()->parentTransactionId->toBeNull()
        ->and($data->transactions->get(1)->parentTransactionId)->toBe($parent->id);
});

it('does not assign parent when only one installment exists', function () {
    $this->actingAs(User::factory()->create());

    $account = Account::factory()->create();

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'PURCHASE STORE',
            'amount' => 15000,
            'currentInstallment' => 1,
            'totalInstallments' => 3,
        ]);

    $data = app()->make(AssignParentTransaction::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect([$transaction])->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    expect($data)
        ->transactions->first()->parentTransactionId->toBeNull();
});

it('does not assign parent to single installment transactions', function () {
    $this->actingAs(User::factory()->create());

    $account = Account::factory()->create();

    $transaction = factory(TransactionDataFactory::class)
        ->create(attributes: [
            'accountId' => $account->id,
            'description' => 'SINGLE PURCHASE',
            'amount' => 10000,
            'currentInstallment' => 1,
            'totalInstallments' => 1,
        ]);

    $data = app()->make(AssignParentTransaction::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect([$transaction])->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    expect($data)
        ->transactions->first()->parentTransactionId->toBeNull();
});

it('groups installments by signature correctly', function () {
    $this->actingAs(User::factory()->create());

    $account = Account::factory()->create();

    // Two different purchase series with same amount but different descriptions
    $transactions = collect([
        factory(TransactionDataFactory::class)->create(attributes: [
            'accountId' => $account->id,
            'description' => 'NETFLIX SUBSCRIPTION',
            'amount' => 5000,
            'currentInstallment' => 1,
            'totalInstallments' => 2,
        ]),
        factory(TransactionDataFactory::class)->create(attributes: [
            'accountId' => $account->id,
            'description' => 'NETFLIX SUBSCRIPTION',
            'amount' => 5000,
            'currentInstallment' => 2,
            'totalInstallments' => 2,
        ]),
        factory(TransactionDataFactory::class)->create(attributes: [
            'accountId' => $account->id,
            'description' => 'SPOTIFY PREMIUM',
            'amount' => 5000,
            'currentInstallment' => 1,
            'totalInstallments' => 2,
        ]),
        factory(TransactionDataFactory::class)->create(attributes: [
            'accountId' => $account->id,
            'description' => 'SPOTIFY PREMIUM',
            'amount' => 5000,
            'currentInstallment' => 2,
            'totalInstallments' => 2,
        ]),
    ]);

    $data = app()->make(AssignParentTransaction::class)
        ->handle(
            new SynchronizationData(
                token: '::fake::',
                bank: app()->make(Bank::class),
                account: $account,
                transactions: collect($transactions)->lazy(),
            ),
            fn (SynchronizationData $data): SynchronizationData => $data
        );

    $netflixParent = $data->transactions->first(fn ($t) => str_contains($t->description, 'NETFLIX') && $t->currentInstallment === 1);
    $spotifyParent = $data->transactions->first(fn ($t) => str_contains($t->description, 'SPOTIFY') && $t->currentInstallment === 1);

    expect($data->transactions->get(0)->parentTransactionId)->toBeNull()
        ->and($data->transactions->get(1)->parentTransactionId)->toBe($netflixParent->id)
        ->and($data->transactions->get(2)->parentTransactionId)->toBeNull()
        ->and($data->transactions->get(3)->parentTransactionId)->toBe($spotifyParent->id);
});

