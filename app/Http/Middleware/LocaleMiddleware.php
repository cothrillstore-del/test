<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if locale is in session
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
        // Check if user has preferred locale
        elseif (auth()->check() && auth()->user()->locale) {
            app()->setLocale(auth()->user()->locale);
        }
        // Check browser language
        elseif ($request->hasHeader('Accept-Language')) {
            $locale = substr($request->header('Accept-Language'), 0, 2);
            if (in_array($locale, ['en', 'vi'])) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}