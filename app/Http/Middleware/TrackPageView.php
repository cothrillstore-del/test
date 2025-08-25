<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Analytics;

class TrackPageView
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track page view after response
        if ($request->isMethod('GET') && !$request->ajax()) {
            $this->trackView($request);
        }

        return $response;
    }

    /**
     * Track the page view
     */
    protected function trackView(Request $request)
    {
        try {
            Analytics::create([
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
                'session_id' => session()->getId(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::error('Failed to track page view: ' . $e->getMessage());
        }
    }
}