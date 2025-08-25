<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // Check if user has backend access
        if (!$user->hasBackendAccess()) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access the admin panel.');
        }

        // Check specific roles if provided
        if (!empty($roles)) {
            if (!in_array($user->role, $roles)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}