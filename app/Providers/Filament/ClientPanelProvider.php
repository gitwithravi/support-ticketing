<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\GravatarProvider;
use App\Filament\Client\Pages\Auth\Login;
use App\Filament\Client\Pages\Auth\Register;
use App\Filament\Client\Pages\Auth\VerifyOtp;
use App\Filament\Client\Pages\EditProfile;
use App\Filament\Client\Widgets\CommonIssues;
use App\Filament\Client\Widgets\LookingForSomethingElse;
use App\Http\Middleware\BypassPolicies;
use App\Http\Middleware\DebugClientAuth;
use App\Http\Middleware\EnsureUserIsActive;
use App\Settings\GeneralSettings;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ClientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $generalSettings = app(GeneralSettings::class);

        try {
            $font = $generalSettings->branding_primary_font;
            $brandFavicon = ! empty($generalSettings->branding_favicon)
                ? Storage::url($generalSettings->branding_favicon)
                : asset('favicon.png');
            $brandLogoBlack = ! empty($generalSettings->branding_logo_black)
                ? Storage::url($generalSettings->branding_logo_black)
                : asset('img/logo/logo-black.png');
            $brandLogoWhite = ! empty($generalSettings->branding_logo_white)
                ? Storage::url($generalSettings->branding_logo_white)
                : asset('img/logo/logo-white.png');
        } catch (QueryException $e) {
            $font = 'Lexend';
            $brandFavicon = asset('favicon.png');
            $brandLogoBlack = asset('img/logo/logo-black.png');
            $brandLogoWhite = asset('img/logo/logo-white.png');
        }

        return $panel
            ->id('client')
            ->path('client')
            ->login(Login::class)
            ->profile(EditProfile::class)
            ->registration(Register::class)
            ->passwordReset()
            ->emailVerification(false)
            ->authGuard('client')
            ->authPasswordBroker('clients')
            ->font($font, provider: GoogleFontProvider::class)
            ->viteTheme('resources/css/filament/app/theme.css')
            ->darkMode(false)
            ->favicon($brandFavicon)
            ->brandLogo(fn () => Auth::guard('client')->guest()
                ? $brandLogoBlack
                : $brandLogoWhite)
            ->brandLogoHeight('3.5rem')
            ->topNavigation()
            ->navigationItems([
                NavigationItem::make('submitATicket')
                    ->url('/client/tickets/create', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-bolt')
                    ->label(__('Submit a ticket'))
                    ->sort(3),
            ])
            ->defaultAvatarProvider(GravatarProvider::class)
            ->discoverResources(
                in: app_path('Filament/Client/Resources'),
                for: 'App\\Filament\\Client\\Resources',
            )
            ->discoverPages(
                in: app_path('Filament/Client/Pages'),
                for: 'App\\Filament\\Client\\Pages',
            )
            ->pages([
                VerifyOtp::class,
            ])
            ->discoverClusters(
                in: app_path('Filament/Client/Clusters'),
                for: 'App\\Filament\\Client\\Clusters',
            )
            ->pages([])
            ->discoverWidgets(
                in: app_path('Filament/Client/Widgets'),
                for: 'App\\Filament\\Client\\Widgets',
            )
            ->widgets([
                CommonIssues::class,
                LookingForSomethingElse::class,
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
                DebugClientAuth::class,
                EnsureUserIsActive::class,
                BypassPolicies::class,
            ])
            ->plugins([])
            ->renderHook(
                PanelsRenderHook::TOPBAR_AFTER,
                fn (): View => view('filament.custom-header'),
            );
    }
}
