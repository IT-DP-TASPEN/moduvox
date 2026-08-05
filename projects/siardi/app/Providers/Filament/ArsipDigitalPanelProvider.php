<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Pages\Dashboard;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ArsipDigitalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('arsip_digital')
            ->path('arsip_digital')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('SIARDI')
            ->viteTheme('resources/css/filament/arsip_digital/theme.css')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite('resources/js/app.js')"),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
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
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => Blade::render('<div style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280; font-weight: 500;">Demo Mode Aplikasi Arsip Digital PT Moduvox Tech ID</div>'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("
                    @vite('resources/js/app.js')
                    @if(request()->routeIs('filament.arsip_digital.auth.login'))
                    <style>
                        body {
                            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #3b82f6 100%) !important;
                            background-attachment: fixed !important;
                        }
                        .fi-logo {
                            color: #ffffff !important;
                            font-size: 2.25rem !important;
                            font-weight: 800 !important;
                            letter-spacing: -0.025em !important;
                            text-shadow: 0 4px 6px rgba(0,0,0,0.3) !important;
                        }
                        main > div > section {
                            background: rgba(255, 255, 255, 0.95) !important;
                            backdrop-filter: blur(16px) !important;
                            -webkit-backdrop-filter: blur(16px) !important;
                            border: 1px solid rgba(255, 255, 255, 0.3) !important;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3) !important;
                            border-radius: 1.5rem !important;
                            padding: 2.5rem !important;
                        }
                        /* Dark mode support inside the box */
                        .dark main > div > section {
                            background: rgba(30, 41, 59, 0.85) !important;
                            border: 1px solid rgba(255, 255, 255, 0.1) !important;
                        }
                        button[type='submit'] {
                            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
                            border: none !important;
                            color: #ffffff !important;
                            font-weight: 600 !important;
                            transition: all 0.3s ease !important;
                            border-radius: 0.75rem !important;
                        }
                        button[type='submit']:hover {
                            transform: translateY(-2px) !important;
                            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4) !important;
                        }
                    </style>
                    @endif
                "),
            );
    }
}
