<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BrokerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.broker.key');
        $provided = $request->header('X-Broker-Api-Key');

        if (empty($expected) || !is_string($provided) || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
