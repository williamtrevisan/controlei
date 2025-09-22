<?php

namespace App\Filament\Imports;

use App\Enums\TransactionDirection;
use App\Enums\TransactionKind;
use App\Enums\TransactionPaymentMethod;
use App\Models\Account;
use App\Models\Card;
use App\Models\IncomeSource;
use App\Models\Transaction;
use App\Services\Contracts\InstallmentsGenerator;
use App\ValueObjects\StatementPeriod;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class TransactionImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('date')
                ->label('Data')
                ->castStateUsing(fn (string $state) => Carbon::parse($state)),

            ImportColumn::make('description')
                ->label('Descrição')
                ->guess(['lançamento']),

            ImportColumn::make('amount')
                ->label('Valor')
                ->numeric(),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            TextInput::make('statement_period')
                ->label('Período do extrato (AAAA-MM)')
                ->placeholder('2025-08')
                ->regex('/^\d{4}-\d{2}$/')
                ->required(),

            Select::make('account_id')
                ->label('Conta')
                ->options(Account::query()->pluck('name', 'id'))
                ->searchable()
                ->reactive()
                ->required()
                ->placeholder('Selecione a conta'),

            Select::make('card_id')
                ->label('Cartão')
                ->options(function (Get $get) {
                    $accountId = $get('account_id');
                    if (! $accountId) {
                        return [];
                    }

                    return Card::query()
                        ->where('account_id', $accountId)
                        ->pluck('last_four_digits', 'id');
                })
                ->searchable()
                ->nullable()
                ->disabled(fn (Get $get) => blank($get('account_id')))
                ->placeholder('Selecione um cartão vinculado a conta'),
        ];
    }

    public function resolveRecord(): ?Transaction
    {
        $data = $this->getData();

        $description = (string) $data['description'];
        $amount = (float) $data['amount'];

        [$_, $installments] = str($description)
            ->split('/(?=\d{1,2}\/\d{1,2}$)/', 2)
            ->pad(2, null);
        [$currentInstallment] = str($installments)
            ->when(
                !is_null($installments),
                fn ($installments) => $installments->explode('/'),
                fn () => [null, null],
            );

        $attributes = [
            'account_id' => $this->options['account_id'] ?? null,
            'card_id' => $this->options['card_id'] ?? null,
            'date' => $data['date'],
            'description' => $description,
            'amount' => $amount,
            'current_installment' => $currentInstallment,
        ];

        if (Transaction::wouldBeDuplicate($attributes)) {
            return null;
        }

        return new Transaction();
    }

    public function fillRecord(): void
    {
        $record = $this->getRecord();
        $data = $this->getData();

        $description = (string) $data['description'];
        $amount = (float)  $data['amount'];

        $incomeSource = IncomeSource::query()
            ->get(['id', 'matcher_regex'])
            ->first(
                fn (IncomeSource $incomeSource): bool
                    => str($description)->isMatch($incomeSource->matcher_regex)
            );

        $paymentMethod = TransactionPaymentMethod::fromTransactionDescription($description);

        [$_, $installaments] = str($description)
            ->split('/(?=\d{1,2}\/\d{1,2}$)/', 2)
            ->pad(2, null);
        [$currentInstallment, $totalInstallments] = str($installaments)
            ->when(
                ! is_null($installaments) && ! $paymentMethod->isPix(),
                fn ($installments) => $installments->explode('/'),
                fn () => [null, null],
            );

        $kind = TransactionKind::fromTransaction(
            [$data['date'], $description, $amount],
            function () use ($description) {
                $prefix = Str::of($description)->limit(10, '')->value();
                return Transaction::query()
                    ->where('description', 'like', $prefix . '%')
                    ->exists();
            }
        );

        $record->account_id = $this->options['account_id'] ?? null;
        $record->card_id = $this->options['card_id'] ?? null;
        $record->income_source_id = $incomeSource?->id;
        $record->date = $data['date'];
        $record->description = $description;
        $record->amount = $amount;
        $record->direction = $amount >= 0 ? TransactionDirection::Inflow : TransactionDirection::Outflow;;
        $record->kind = $kind;
        $record->payment_method = $paymentMethod;
        $record->current_installment = $currentInstallment;
        $record->total_installments = $totalInstallments;
        $record->statement_period = new StatementPeriod($this->options['statement_period']) ?? null;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->getFailedRowsCount();

        $parts = [];

        $parts[] = sprintf(
            'A importação de transações foi concluída: %s %s %s importad%s',
            Number::format($successful),
            Str::plural('linha', $successful),
            $successful === 1 ? 'foi' : 'foram',
            $successful === 1 ? 'a' : 'as'
        );

        if ($failed > 0) {
            $parts[] = sprintf(
                '%s %s não %s importad%s',
                Number::format($failed),
                Str::plural('linha', $failed),
                $failed === 1 ? 'foi' : 'foram',
                $failed === 1 ? 'a' : 'as'
            );
        }

        return implode('. ', $parts) . '.';
    }

//    protected function afterSave(): void
//    {
//        app()->make(InstallmentsGenerator::class)
//            ->generate($this->getRecord());
//    }
}
