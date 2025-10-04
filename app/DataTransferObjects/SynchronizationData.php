<?php

namespace App\DataTransferObjects;

use App\Models\Account;
use Banklink\Contracts\Bank;
use Banklink\Entities\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

readonly class SynchronizationData
{
    public function __construct(
        public string $token,
        public ?Bank $bank = null,
        public ?Account $account = null,
        public ?LazyCollection $accountTransactions = null,
        public ?LazyCollection $cardTransactions = null,
        /**
         * @var LazyCollection<string, string>|null
         */
        public ?LazyCollection $statementMap = null, // hash => statement_id
        /**
         * @var LazyCollection<int, TransactionData>|null
         */
        public ?LazyCollection $transactions = null,
    ) {
    }

    public static function from(string $token): self
    {
        return new self(token: $token);
    }

    public function withBank(Bank $bank): self
    {
        return new self(
            token: $this->token,
            bank: $bank,
        );
    }

    public function withAccount(Account $account): self
    {
        return new self(
            token: $this->token,
            bank: $this->bank,
            account: $account,
        );
    }

    /**
     * @param LazyCollection<int, Transaction> $transactions
     * @return self
     */
    public function withAccountTransactions(LazyCollection $transactions): self
    {
        return new self(
            token: $this->token,
            bank: $this->bank,
            account: $this->account,
            accountTransactions: $transactions,
            cardTransactions: $this->cardTransactions,
            statementMap: $this->statementMap,
        );
    }

    /**
     * @param LazyCollection<int, Transaction> $transactions
     * @return self
     */
    public function withCardTransactions(LazyCollection $transactions): self
    {
        return new self(
            token: $this->token,
            bank: $this->bank,
            account: $this->account,
            accountTransactions: $this->accountTransactions,
            cardTransactions: $transactions,
            statementMap: $this->statementMap,
        );
    }

    public function withStatementMap(LazyCollection $statementMap): self
    {
        return new self(
            token: $this->token,
            bank: $this->bank,
            account: $this->account,
            accountTransactions: $this->accountTransactions,
            cardTransactions: $this->cardTransactions,
            statementMap: $statementMap,
        );
    }

    public function withTransactions(LazyCollection $transactions): self
    {
        return new self(
            token: $this->token,
            bank: $this->bank,
            account: $this->account,
            accountTransactions: $this->accountTransactions,
            cardTransactions: $this->cardTransactions,
            statementMap: $this->statementMap,
            transactions: $transactions,
        );
    }
}
