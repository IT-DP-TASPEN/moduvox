<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (env('APP_ENV') !== 'demo') {
            return $next($request);
        }

        $method = $request->method();
        $path = $request->path();

        // Allowed POST/PUT/DELETE paths in demo mode
        // Login and logout must work to allow access.
        if (in_array($path, ['login', 'logout']) || str_contains($path, 'login')) {
            return $next($request);
        }

        // We only intercept modifying requests
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            
            // Check if it's an AJAX/JSON request
            if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'status' => 'success',
                    'message' => 'Demo Mode: Tindakan berhasil disimulasikan.',
                    'data' => []
                ]);
            }

            // Normal form submission
            // We flash a success message and redirect back.
            // Some controllers like store() might expect to redirect to index instead of back.
            // But redirecting back is universally safe and prevents the user from noticing a missing record on index page.
            
            // Wait, for 'import', we redirect back. For 'store', redirecting back goes to the 'create' form.
            // To simulate success perfectly without showing the form again, we can try to guess the index route,
            // or we just redirect back. Redirecting back is fine for a demo mode.
            
            $message = 'Demo Mode: Tindakan berhasil disimulasikan (Data tidak disimpan).';
            
            if (str_contains($path, 'import')) {
                $message = 'Demo Mode: Data berhasil di-import.';
            } elseif (str_contains($path, 'approve') || str_contains($path, 'reject')) {
                $message = 'Demo Mode: Status berhasil diubah.';
            }

            return redirect()->back()->with('success', $message);
        }

        return $next($request);
    }
}
