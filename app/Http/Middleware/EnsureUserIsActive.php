<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get user from the client guard since this middleware is used in client panel
        $user = $request->user('client');

        logger('EnsureUserIsActive middleware', [
            'path' => $request->path(),
            'has_user' => ! is_null($user),
            'user_id' => $user?->id,
            'is_active' => $user?->is_active,
            'email_verified' => $user?->hasVerifiedEmail(),
            'is_client' => $user instanceof \App\Models\Client,
        ]);

        // If no user, let the auth middleware handle it
        if (! $user) {
            return $next($request);
        }

        // Allow access to OTP verification page for unverified clients
        if ($request->is('client/verify-otp') && ! $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Check if user is active
        if (! $user->is_active) {
            logger('User is not active, logging out', ['user_id' => $user->id]);
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'data.email' => __('Your account is inactive. Please contact your administrator.'),
            ]);
        }

        // For clients, also check email verification
        if ($user instanceof \App\Models\Client && ! $user->hasVerifiedEmail()) {
            logger('User email not verified, redirecting to OTP', ['user_id' => $user->id]);

            return redirect('/client/verify-otp');
        }

        return $next($request);
    }
}
