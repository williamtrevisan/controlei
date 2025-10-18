<?php

use App\Actions\CategorizeManyTransactions;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('assigns category id to transactions', function () {
    $this->actingAs($user = User::factory()->create());

    $account = Account::factory()
        ->for($user)
        ->create();

    $transactions = Transaction::factory(count: 2)
        ->for($account)
        ->sequence(
            ['id' => $first = (string) Str::uuid7(), 'category_id' => 8],
            ['id' => $last = (string) Str::uuid7(), 'category_id' => 8],
        )
        ->createQuietly();

    Http::fake([
        '*/categorize' => Http::response(implode("\n", [
            json_encode(['id' => $first, 'category_id' => 1]),
            json_encode(['id' => $last, 'category_id' => 6]),
        ]), headers: [
            'Content-Type' => 'application/x-ndjson',
        ]),
    ]);

    app()->make(CategorizeManyTransactions::class)
        ->execute($transactions);

    $this->assertDatabaseHas(Transaction::class, ['id' => $first, 'category_id' => 1]);
    $this->assertDatabaseHas(Transaction::class, ['id' => $last, 'category_id' => 6]);

    Http::assertSent(function ($request) use ($transactions) {
        $payload = $request->data();
        
        expect($payload)->toHaveKey('transactions');
        expect($payload['transactions'])->toHaveCount(2);
        
        $firstTransaction = $payload['transactions'][0];
        $transaction = $transactions->first();
        
        expect($firstTransaction)
            ->toHaveKey('id')
            ->toHaveKey('description')
            ->toHaveKey('direction')
            ->toHaveKey('amount')
            ->toHaveKey('kind')
            ->toHaveKey('payment_method')
            ->toHaveKey('total_installments');
        
        expect($firstTransaction['amount'])->toBe($transaction->amount->getMinorAmount()->toInt());
        expect($firstTransaction['kind'])->toBe($transaction->kind->value);
        expect($firstTransaction['payment_method'])->toBe($transaction->payment_method->value);
        
        return true;
    });
});
