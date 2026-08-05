<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DemoModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For GET requests, we don't need to wrap in transaction since they shouldn't modify data
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        // Start transaction to prevent permanent DB writes
        DB::beginTransaction();

        try {
            $response = $next($request);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Always rollback for POST/PUT/DELETE to simulate success without permanent changes
        DB::rollBack();

        return $response;
    }
}
