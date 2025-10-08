<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Invites\InviteResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Components\Section;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
