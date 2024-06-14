<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CircuitBreakerMiddleware
{
    const FAILURE_THRESHOLD = 5;
    const RETRY_TIMEOUT = 30; // seconds

    public function handle($request, Closure $next)
    {
        $service = 'serviceb';
        $failures = Cache::get("{$service}_failures", 0);
        $lastAttempt = Cache::get("{$service}_last_attempt", now());

        if ($failures >= self::FAILURE_THRESHOLD && now()->diffInSeconds($lastAttempt) < self::RETRY_TIMEOUT) {
            return response()->json(['error' => 'Service B is currently unavailable due to repeated failures'], 503);
        }

        $response = $next($request);

        if ($response->status() >= 500) {
            Cache::increment("{$service}_failures");
            Cache::put("{$service}_last_attempt", now());
        } else {
            Cache::forget("{$service}_failures");
            Cache::forget("{$service}_last_attempt");
        }

        return $response;
    }
}
