<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Card;
use App\Models\Category;
use App\Models\IncomeSource;
use App\Models\Invite;
use App\Models\Statement;
use App\Models\Transaction;
use App\Models\TransactionMember;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use App\Repositories\AccountEloquentRepository;
use App\Repositories\CategoryEloquentRepository;
use App\Repositories\Contracts\AccountRepository;
use App\Repositories\Contracts\CardRepository;
use App\Repositories\Contracts\CategoryRepository;
use App\Repositories\Contracts\IncomeSourceRepository;
use App\Repositories\Contracts\InviteRepository;
use App\Repositories\Contracts\NotificationRepository;
use App\Repositories\Contracts\StatementRepository;
use App\Repositories\Contracts\TransactionMemberRepository;
use App\Repositories\Contracts\TransactionRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\IncomeSourceEloquentRepository;
use App\Repositories\InviteEloquentRepository;
use App\Repositories\NotificationEloquentRepository;
use App\Repositories\StatementEloquentRepository;
use App\Repositories\TransactionEloquentRepository;
use App\Repositories\TransactionMemberEloquentRepository;
use App\Repositories\UserEloquentRepository;
use App\Services\Contracts;
use App\Services\InstallmentGenerator;
use Filament\Pages\Page;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->scoped(Contracts\InstallmentsGenerator::class, InstallmentGenerator::class);

        $this->app->singleton(IncomeSourceRepository::class, fn () => new IncomeSourceEloquentRepository(new IncomeSource()));
        $this->app->singleton(AccountRepository::class, fn () => new AccountEloquentRepository(new Account()));
        $this->app->singleton(StatementRepository::class, fn () => new StatementEloquentRepository(new Statement()));
        $this->app->singleton(TransactionRepository::class, fn () => new TransactionEloquentRepository(new Transaction()));
        $this->app->singleton(CategoryRepository::class, fn () => new CategoryEloquentRepository(new Category()));
        $this->app->singleton(InviteRepository::class, fn () => new InviteEloquentRepository(new Invite()));
        $this->app->singleton(TransactionMemberRepository::class, fn () => new TransactionMemberEloquentRepository(new TransactionMember()));
        $this->app->singleton(UserRepository::class, fn () => new UserEloquentRepository(new User()));
        $this->app->singleton(NotificationRepository::class, fn () => new NotificationEloquentRepository(new DatabaseNotification()));
    }

    public function boot(): void
    {
        Page::alignFormActionsEnd();
    }
}
