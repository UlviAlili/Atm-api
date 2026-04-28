<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MeasureApiPerformance
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $response->headers->set('X-Response-Time-Ms', $duration);

        if ($duration > 500) {
            Log::warning('Slow API request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration_ms' => $duration,
                'user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}