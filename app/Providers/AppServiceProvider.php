<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Card;
use App\Models\IncomeSource;
use App\Models\Transaction;
use App\Observers\TransactionObserver;
use App\Repositories\AccountEloquentRepository;
use App\Repositories\CardEloquentRepository;
use App\Repositories\Contracts\AccountRepository;
use App\Repositories\Contracts\CardRepository;
use App\Repositories\Contracts\IncomeSourceRepository;
use App\Repositories\Contracts\TransactionRepository;
use App\Repositories\IncomeSourceEloquentRepository;
use App\Repositories\TransactionEloquentRepository;
use App\Services\Contracts;
use App\Services\InstallmentGenerator;
use Filament\Pages\Page;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Contracts\InstallmentsGenerator::class, InstallmentGenerator::class);

        $this->app->singleton(IncomeSourceRepository::class, fn () => new IncomeSourceEloquentRepository(new IncomeSource()));
        $this->app->singleton(AccountRepository::class, fn () => new AccountEloquentRepository(new Account()));
        $this->app->singleton(CardRepository::class, fn () => new CardEloquentRepository(new Card()));
        $this->app->singleton(TransactionRepository::class, fn () => new TransactionEloquentRepository(new Transaction()));
    }

    public function boot(): void
    {
        Page::alignFormActionsEnd();

        Transaction::observe(TransactionObserver::class);
    }
}
