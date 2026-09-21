<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AdminSettings;

class ResultsApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Results-API-Key');
        $settings = AdminSettings::first();

        if (!$settings || !$settings->results_api_key || $apiKey !== $settings->results_api_key) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing API Key'
            ], 401);
        }

        return $next($request);
    }
}
