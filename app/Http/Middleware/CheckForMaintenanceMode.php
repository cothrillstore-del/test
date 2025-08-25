<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled in settings
        if (config('app.maintenance_mode', false)) {
            // Allow admin access
            if (auth()->check() && auth()->user()->isAdmin()) {
                return $next($request);
            }

            // Allow specific IPs (for developers)
            $allowedIps = config('app.maintenance_allowed_ips', []);
            if (in_array($request->ip(), $allowedIps)) {
                return $next($request);
            }

            // Show maintenance page
            return response()->view('errors.maintenance', [], 503);
        }

        return $next($request);
    }
}