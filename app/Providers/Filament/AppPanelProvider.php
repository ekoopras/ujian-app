<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\Auth\CustomLogin;
use App\Filament\App\Pages\Auth\RegisterSiswa;
use App\Filament\App\Pages\DaftarUjian;
use App\Filament\App\Pages\ReviewUjian;
use App\Filament\App\Pages\UjianPage;
use App\Http\Middleware\CheckRole;
use App\Livewire\App\DashboardSiswa;
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
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        // Panggil CDN Tailwind v3 khusus di panel /app
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => '
                <meta name="google" content="notranslate" />
                <script>
                    // Menambahkan atribut translate="no" dan class "notranslate" ke elemen <html> & <body>
                    document.documentElement.setAttribute("translate", "no");
                    document.documentElement.classList.add("notranslate");
                    document.addEventListener("DOMContentLoaded", function() {
                        if (document.body) {
                            document.body.setAttribute("translate", "no");
                            document.body.classList.add("notranslate");
                        }
                    });
                </script>
                <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
                <script>
                    tailwind.config = {
                        darkMode: "class",
                        theme: {
                            extend: {
                                colors: {
                                    primary: {
                                        50: "#eff6ff", 100: "#dbeafe", 200: "#bfdbfe", 300: "#93c5fd",
                                        400: "#60a5fa", 500: "#3b82f6", 600: "#2563eb", 700: "#1d4ed8",
                                        800: "#1e40af", 900: "#1e3a8a", 950: "#172554"
                                    }
                                }
                            }
                        }
                    }
                </script>
                <style>
                    

                    body {
                        -webkit-user-select: none; /* Safari */
                        -ms-user-select: none;     /* IE 10 dan versi setelahnya */
                        user-select: none;         /* Standard syntax */
                    }
                    input, textarea {
                        -webkit-user-select: text;
                        -ms-user-select: text;
                        user-select: text;
                    }
                </style>
                
            '
        );

        return $panel
            ->id('app')
            ->path('app')
            ->login()

            ->homeUrl(fn() => UjianPage::getUrl())
            ->topNavigation()
            ->darkMode(false)
            ->brandName('UjianApp')

            //logo
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn(): string => Blade::render('
                <style>
                    .fi-topbar-open-sidebar-btn {
                            display: none !important;
                        }

                    .fi-topbar-item {
                        display: none !important;
                    }
                    .fi-logo {
                        display: none !important;
                    }
                </style>
                    <div class="flex items-center gap-x-3 pl-2 md:pl-4">
                        <img src="' . asset('ico.jpeg') . '" alt="Logo" class="h-10 w-auto">
                        <span class="text-md font-bold tracking-tight text-gray-900 dark:text-white">
                            
                        </span>
                    </div>
                '),
            )

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                // Pages\Dashboard::class,
                UjianPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
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
                CheckRole::class . ':app',
            ]);
    }
}
