<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Invites\InviteResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->topbar(condition: false)
            ->login()
            ->registration()
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Purple,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch(provider: false)
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => Blade::render(<<<'HTML'
                    <style>
                        .fi-sidebar-account-balance {
                            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.15) !important;
                        }

                        .dark .fi-sidebar-account-balance {
                            box-shadow: 0 1px 3px 0 rgba(255, 255, 255, 0.1), 0 1px 2px 0 rgba(255, 255, 255, 0.15) !important;
                        }
                    </style>

                    <div x-show="! $store.sidebar?.isOpen === false" x-transition>
                        @livewire(\App\Filament\Resources\Accounts\Widgets\AccountBalanceStat::class)
                    </div>
                HTML)
            )
            ->databaseNotifications()
            ->userMenuItems([
                Action::make('invite_code')
                    ->label(fn () => auth()->user()->invite_code)
                    ->icon(Heroicon::OutlinedClipboardDocument)
                    ->color('primary')
                    ->badge('Clique para copiar')
                    ->badgeColor('gray')
                    ->extraAttributes(fn () => [
                        'data-copyable' => auth()->user()->invite_code,
                    ], true)
                    ->alpineClickHandler(<<<'JS'
                        navigator.clipboard
                            .writeText(event.currentTarget.dataset.copyable)
                            .then(() => {
                                $tooltip('Código copiado!', { timeout: 3000 });
                            });
                    JS),

                Action::make('send_invite')
                    ->label('Conecte-se com outros usuários')
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->color('success')
                    ->url(fn () => InviteResource::getUrl('create')),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        Carbon::setLocale('pt_BR');
    }
}
