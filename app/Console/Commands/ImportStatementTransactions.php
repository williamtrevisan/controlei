<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Card;
use App\Services\Importers\Contracts\Importer;
use Illuminate\Console\Command;

class ImportStatementTransactions extends Command
{
    protected $signature = 'transactions:import-statement
        {file : Path to the transactions file}
        {period : The statement period in Y-m format (e.g. 2025-07)}
        {--account= : Optional account ID}
        {--card= : Optional card ID}';

    protected $description = 'Command description';

    public function handle(Importer $statementImporter)
    {
        $statementImporter
            ->account(Account::query()->find($this->option('account')))
            ->card(Card::query()->find($this->option('card')))
            ->import($this->argument('file'), $this->argument('period'));
    }
}
