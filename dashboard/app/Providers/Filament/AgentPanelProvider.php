<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetBrowserTimezone;
use App\Filament\Agent\Pages\AgentDashboard;
use App\Filament\Agent\Pages\ConversationView;
use App\Filament\Agent\Pages\MyActiveChats;
use App\Filament\Agent\Pages\WaitingChats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AgentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('agent')
            ->path('agent')
            ->colors([
                'primary' => Color::hex('#FF3B30'),
            ])
            ->login()
            ->passwordReset()
            ->pages([
                AgentDashboard::class,
                WaitingChats::class,
                MyActiveChats::class,
                ConversationView::class,
            ])
            ->widgets([
                AccountWidget::class,
            ])
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
            );
    }
}
