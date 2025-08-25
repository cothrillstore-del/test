<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log user activity
     */
    protected function logActivity(Request $request, Response $response)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $this->getAction($request),
                'model_type' => $this->getModelType($request),
                'model_id' => $this->getModelId($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Get action from request
     */
    protected function getAction(Request $request)
    {
        $method = $request->method();
        $routeName = $request->route()->getName();
        
        return $routeName ?: $method . ' ' . $request->path();
    }

    /**
     * Get model type from request
     */
    protected function getModelType(Request $request)
    {
        $route = $request->route();
        
        if ($route && $route->parameterNames()) {
            $param = $route->parameterNames()[0] ?? null;
            return $param ? ucfirst($param) : null;
        }
        
        return null;
    }

    /**
     * Get model ID from request
     */
    protected function getModelId(Request $request)
    {
        $route = $request->route();
        
        if ($route && $route->parameters()) {
            $params = $route->parameters();
            return reset($params);
        }
        
        return null;
    }
}