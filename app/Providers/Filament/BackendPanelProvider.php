<?php

namespace App\Providers\Filament;

use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use App\Filament\Widgets\WebInfoWidget;
use App\Filament\Widgets\MaintenanceToggle;
use App\Filament\Resources\ClearCacheWidgetResource\Widgets\ClearCacheWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;


class BackendPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('backend')
            ->path('backend')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName(env('APP_NAME', 'LARAVEL-STARTER'))
            // ->brandLogo(asset('img/logo.png'))
            // ->brandLogoHeight('4rem') // Ubah sesuai kebutuhan (3rem, 4rem, dll)
            ->favicon(asset('img/favicon_io/favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                WebInfoWidget::class,
                MaintenanceToggle::class,
                ClearCacheWidget::class,
              
            ])
            ->navigationGroups([
                'Sistem', // Grup default Filament
                'Pengguna', // Grup default Filament
                'Web Setting', // Grup custom
                'Master Data',      // Grup master                
                'Kepegawaian',      // Grup kepegawaian
               
               
                // Urutan grup sesuai kebutuhan
            ])

            //ini untuk hide menu web setting (bisa di buka kalau digunakan lagi)
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => new HtmlString('
                    <style>
                        [data-group-label="Web Setting"] { display: none !important; }
                        [data-group-label="Sistems"] { display: none !important; }
                        [data-group-label="Posisi"] { display: none !important; }
                    </style>
                ')
            )
           
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
}
