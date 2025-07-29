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
        $user = $request->user();
        
        // Allow access to OTP verification page for unverified clients
        if ($request->is('client/verify-otp') && !$user->hasVerifiedEmail()) {
            return $next($request);
        }
        
        // Check if user is active
        if (!$user->is_active) {
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'data.email' => __('Your account is inactive. Please contact your administrator.'),
            ]);
        }
        
        // For clients, also check email verification
        if ($user instanceof \App\Models\Client && !$user->hasVerifiedEmail()) {
            return redirect('/client/verify-otp');
        }

        return $next($request);
    }
}
