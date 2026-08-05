<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Cif;
use App\Models\MobileAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ─────────────────────────────────────────────────
    //  POST /api/mobile/auth/login
    // ─────────────────────────────────────────────────
    /**
     * Login dengan username + password.
     * Mengembalikan bearer token untuk sesi selanjutnya.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string',
            'password'    => 'required|string',
            'device_id'   => 'nullable|string|max:255',
            'device_name' => 'nullable|string|max:100',
            'fcm_token'   => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        $mobileAccess = MobileAccess::where('username', $request->username)->first();

        if (! $mobileAccess) {
            return $this->errorResponse('Username atau password salah.', null, 401);
        }

        if (! $mobileAccess->activated_at) {
            return $this->errorResponse(
                'Akun belum diaktivasi. Silakan lakukan aktivasi terlebih dahulu.',
                ['code' => 'ACCOUNT_NOT_ACTIVATED'],
                403
            );
        }

        // Cek apakah akun aktif sebelum verifikasi password
        if (! $mobileAccess->is_active) {
            return $this->errorResponse(
                'Akun Anda diblokir. Hubungi petugas BPR untuk membuka blokir.',
                ['code' => 'ACCOUNT_BLOCKED'],
                403
            );
        }

        if (! $mobileAccess->verifyPassword($request->password)) {
            return $this->errorResponse('Username atau password salah.', null, 401);
        }

        // Update device info & fcm_token
        $mobileAccess->update([
            'device_id'    => $request->device_id ?? $mobileAccess->device_id,
            'fcm_token'    => $request->fcm_token ?? $mobileAccess->fcm_token,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $tokenRecord = $mobileAccess->createToken(
            $request->device_id,
            $request->device_name
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token'      => $tokenRecord->plain_token,
                'expires_at' => $tokenRecord->expires_at?->toIso8601String(),
                'cif_no'     => $mobileAccess->cif_no,
                'username'   => $mobileAccess->username,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    //  POST /api/mobile/auth/logout
    // ─────────────────────────────────────────────────
    /**
     * Logout: hapus semua token aktif nasabah.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');
        $mobileAccess->revokeAllTokens();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    // ─────────────────────────────────────────────────
    //  POST /api/mobile/auth/register
    // ─────────────────────────────────────────────────
    /**
     * Pendaftaran akun mobile baru berdasarkan CIF yang sudah ada.
     * Biasanya dipanggil oleh admin/teller, bukan langsung nasabah.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cif_no'   => 'required|string|exists:cifs,cif_no',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        $cif = Cif::where('cif_no', $request->cif_no)->firstOrFail();

        // Cek apakah CIF sudah punya akun mobile
        if (MobileAccess::where('cif_id', $cif->id)->exists()) {
            return $this->errorResponse(
                'CIF ini sudah memiliki akun mobile banking.',
                null,
                409
            );
        }

        $mobileAccess = new MobileAccess([
            'cif_id'  => $cif->id,
            'cif_no'  => $cif->cif_no,
            'username' => null,
            'password_hash' => null,
            'pin_hash' => null,
            'activated_at' => null,
            'is_active' => true,
            'wrong_pin_count' => 0,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $mobileAccess->save();

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran akun mobile berhasil.',
            'data'    => [
                'cif_no'   => $mobileAccess->cif_no,
                'activation_status' => 'PENDING',
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────
    //  POST /api/mobile/auth/change-password
    // ─────────────────────────────────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        if (! $mobileAccess->verifyPassword($request->old_password)) {
            return $this->errorResponse('Password lama tidak sesuai.', null, 422);
        }

        $mobileAccess->setPassword($request->password);
        $mobileAccess->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }

    // ─────────────────────────────────────────────────
    //  POST /api/mobile/auth/verify-activation
    // ─────────────────────────────────────────────────
    public function verifyActivation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nik'   => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        $cif = Cif::where('nik', $request->nik)
            ->where('phone', $request->phone)
            ->where('email', $request->email)
            ->first();

        if (! $cif) {
            return $this->errorResponse('Data identitas tidak ditemukan atau tidak sesuai.', null, 404);
        }

        $mobileAccess = MobileAccess::where('cif_id', $cif->id)->first();

        if (! $mobileAccess) {
            return $this->errorResponse('Anda belum diberikan akses Mobile Banking. Hubungi petugas cabang.', null, 403);
        }

        if ($mobileAccess->activated_at) {
            return $this->errorResponse('Akun Anda sudah aktif. Silakan langsung login.', null, 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Identitas terverifikasi.',
            'data'    => [
                'cif_no' => $cif->cif_no,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    //  POST /api/mobile/auth/activate
    // ─────────────────────────────────────────────────
    public function activate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cif_no'   => 'required|string|exists:cifs,cif_no',
            'username' => 'required|string|min:6|max:50|unique:mobile_access,username',
            'password' => 'required|string|min:8|confirmed',
            'pin'      => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        $mobileAccess = MobileAccess::where('cif_no', $request->cif_no)->first();
        if (! $mobileAccess) {
             return $this->errorResponse('Akses mobile belum diberikan.', null, 403);
        }
        if ($mobileAccess->activated_at) {
            return $this->errorResponse('Akun sudah diaktivasi. Silakan login.', null, 409);
        }

        $mobileAccess->username = $request->username;
        $mobileAccess->setPassword($request->password);
        $mobileAccess->setPin($request->pin);
        $mobileAccess->is_active = true;
        $mobileAccess->activated_at = now();
        $mobileAccess->save();

        return response()->json([
            'success' => true,
            'message' => 'Aktivasi berhasil, silakan login.',
        ]);
    }

    // ─────────────────────────────────────────────────
    //  Helper
    // ─────────────────────────────────────────────────
    private function errorResponse(string $message, mixed $errors = null, int $status = 400): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $status);
    }
}
