<?php

namespace App\Pipelines\TransactionSynchronization;

use App\DataTransferObjects\SynchronizationData;
use App\Pipelines\TransactionSynchronization\Actions\AssignAccountTransactionStatement;
use App\Pipelines\TransactionSynchronization\Actions\AssignCardTransactionStatement;
use App\Pipelines\TransactionSynchronization\Actions\AssignCategory;
use App\Pipelines\TransactionSynchronization\Actions\AssignExpense;
use App\Pipelines\TransactionSynchronization\Actions\AssignIncomeSource;
use App\Pipelines\TransactionSynchronization\Actions\AssignParentTransaction;
use App\Pipelines\TransactionSynchronization\Actions\ConnectBank;
use App\Pipelines\TransactionSynchronization\Actions\CreateData;
use App\Pipelines\TransactionSynchronization\Actions\ExtractAccountTransactions;
use App\Pipelines\TransactionSynchronization\Actions\ExtractCardTransactions;
use App\Pipelines\TransactionSynchronization\Actions\FindOrCreateAccount;
use App\Pipelines\TransactionSynchronization\Actions\FindOrCreateManyCards;
use App\Pipelines\TransactionSynchronization\Actions\FindOrCreateManyStatements;
use App\Pipelines\TransactionSynchronization\Actions\UpdateAccountBalance;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class GetAllBankTransactions
{
    public function execute(string $token): LazyCollection
    {
        return DB::transaction(function () use ($token) {
            $data = app()->make(Pipeline::class)
                ->send(SynchronizationData::from($token))
                ->through([
                    ConnectBank::class,
                    FindOrCreateAccount::class,
                    FindOrCreateManyCards::class,
                    FindOrCreateManyStatements::class,
                    ExtractAccountTransactions::class,
                    AssignAccountTransactionStatement::class,
                    UpdateAccountBalance::class,
                    ExtractCardTransactions::class,
                    AssignCardTransactionStatement::class,
                    CreateData::class,
                    AssignIncomeSource::class,
                    AssignExpense::class,
                    AssignParentTransaction::class,
                ])
                ->thenReturn();

            return $data->transactions;
        });
    }
}
