<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use App\Filament\Client\ClientDashboard;
use Filament\Panel;
use App\Http\Middleware\SetBrowserTimezone;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ClientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('client')
            ->path('client')
            ->darkMode(false)
            ->brandName('Client Workspace')
            ->colors([
                'primary' => Color::hex('#E9493E'),
            ])
            ->navigationGroups([
                NavigationGroup::make('Chatbot')
                    ->collapsible(),
                NavigationGroup::make('Live Chat')
                    ->collapsible(),
            ])
            ->discoverResources(in: app_path('Filament/Client/Resources'), for: 'App\Filament\Client\Resources')
            ->discoverPages(in: app_path('Filament/Client/Pages'), for: 'App\Filament\Client\Pages')
            ->login()
            ->passwordReset()
            ->pages([
                ClientDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Client/Widgets'), for: 'App\Filament\Client\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetBrowserTimezone::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.auth.partials.login-theme')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): string => view('filament.auth.partials.login-brand')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => view('filament.client.partials.powered-by')->render(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.client.partials.theme')->render(),
            )
            ->renderHook(
    PanelsRenderHook::BODY_END,
    fn (): string => Blade::render(<<<'BLADE'
        <script>
            (() => {
                const timezone =
                    Intl.DateTimeFormat()
                        .resolvedOptions()
                        .timeZone;

                if (!timezone) {
                    return;
                }

                const currentTimezone =
                    document.cookie
                        .split('; ')
                        .find(row =>
                            row.startsWith('browser_timezone=')
                        )
                        ?.split('=')[1];

                if (
                    decodeURIComponent(currentTimezone || '')
                    === timezone
                ) {
                    return;
                }

                document.cookie =
                    'browser_timezone=' +
                    encodeURIComponent(timezone) +
                    '; path=/; max-age=31536000; SameSite=Lax';

                window.location.reload();
            })();
        </script>
    BLADE),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.client.partials.password-reset-modal')->render(),
            );
    }
}
