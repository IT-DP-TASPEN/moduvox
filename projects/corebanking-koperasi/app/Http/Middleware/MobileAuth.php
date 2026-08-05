<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MobileToken;
use Symfony\Component\HttpFoundation\Response;

class MobileAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return response()->json([
                'success' => false,
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Token tidak ditemukan. Silakan login terlebih dahulu.',
            ], 401);
        }

        $tokenHash = hash('sha256', $bearerToken);

        $tokenRecord = MobileToken::with('mobileAccess')
            ->where('token', $tokenHash)
            ->first();

        if (! $tokenRecord) {
            $tokenRecord = MobileToken::with('mobileAccess')
                ->where('token', $bearerToken)
                ->first();

            if ($tokenRecord && $tokenRecord->isValid()) {
                $tokenRecord->token = $tokenHash;
                $tokenRecord->save();
            }
        }

        if (! $tokenRecord || ! $tokenRecord->isValid()) {
            return response()->json([
                'success' => false,
                'code'    => 'TOKEN_EXPIRED',
                'message' => 'Token tidak valid atau telah kedaluwarsa. Silakan login kembali.',
            ], 401);
        }

        $mobileAccess = $tokenRecord->mobileAccess;

        if (! $mobileAccess || ! $mobileAccess->is_active) {
            return response()->json([
                'success' => false,
                'code'    => 'ACCOUNT_BLOCKED',
                'message' => 'Akun Anda telah diblokir. Hubungi petugas untuk pembukaan blokir.',
            ], 403);
        }

        // Update last_used_at token
        $tokenRecord->updateLastUsed();

        // Inject ke request agar mudah diakses di controller
        $request->merge(['_mobile_access' => $mobileAccess]);
        $request->attributes->set('mobile_access', $mobileAccess);

        return $next($request);
    }
}
