<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $instanceApiKey = config('services.evolution.instance_token');

        if (empty($upCommingInstanceApiKey) || $upCommingInstanceApiKey !== $instanceApiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
