<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BypassPolicies
{
    public function handle(Request $request, Closure $next)
    {
        Gate::before(function () {
            return true;
        });

        return $next($request);
    }
}
