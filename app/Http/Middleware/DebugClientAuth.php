<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebugClientAuth
{
    public function handle(Request $request, Closure $next)
    {
        logger('DebugClientAuth middleware', [
            'path' => $request->path(),
            'method' => $request->method(),
            'auth_guard_client_check' => Auth::guard('client')->check(),
            'auth_guard_client_id' => Auth::guard('client')->id(),
            'filament_auth_check' => Filament::auth()->check(),
            'filament_auth_id' => Filament::auth()->id(),
            'session_id' => session()->getId(),
            'has_login_session' => session()->has('login_client_'.Auth::guard('client')->getName()),
        ]);

        return $next($request);
    }
}
