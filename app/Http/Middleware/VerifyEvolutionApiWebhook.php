<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
class VerifyEvolutionApiWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $upCommingInstanceApiKey = $request->input('apikey');
        Log::info($request->all());
        $instanceApiKey = config('services.evolution.instance_token');
        Log::info($upCommingInstanceApiKey);
        Log::info($instanceApiKey);

        if (empty($upCommingInstanceApiKey) || $upCommingInstanceApiKey !== $instanceApiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
