<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobileAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PinController extends Controller
{
    // ─────────────────────────────────────────────────
    //  POST /api/mobile/pin/verify
    // ─────────────────────────────────────────────────
    /**
     * Verifikasi PIN nasabah.
     * Logika lockout: 3x salah → is_active = false.
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'pin.size'  => 'PIN harus tepat 6 digit angka.',
            'pin.regex' => 'PIN hanya boleh berisi angka.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        $isCorrect = $mobileAccess->verifyPin($request->pin);

        if (! $isCorrect) {
            // Reload setelah save di verifyPin()
            $mobileAccess->refresh();

            $remaining = max(0, 3 - $mobileAccess->wrong_pin_count);

            if (! $mobileAccess->is_active) {
                return response()->json([
                    'success' => false,
                    'code'    => 'ACCOUNT_BLOCKED',
                    'message' => 'Akun Anda telah diblokir karena 3x percobaan PIN salah. Hubungi petugas BPR.',
                ], 403);
            }

            return response()->json([
                'success'           => false,
                'code'              => 'WRONG_PIN',
                'message'           => "PIN salah. Sisa percobaan: {$remaining}x.",
                'wrong_pin_count'   => $mobileAccess->wrong_pin_count,
                'remaining_attempts' => $remaining,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'PIN valid.',
        ]);
    }

    // ─────────────────────────────────────────────────
    //  POST /api/mobile/pin/change
    // ─────────────────────────────────────────────────
    /**
     * Ganti PIN — memerlukan PIN lama untuk konfirmasi.
     */
    public function change(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_pin'     => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'new_pin'     => ['required', 'string', 'size:6', 'regex:/^\d{6}$/', 'different:old_pin'],
            'new_pin_confirmation' => ['required', 'string', 'size:6'],
        ], [
            'new_pin.different' => 'PIN baru tidak boleh sama dengan PIN lama.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        if ($request->new_pin !== $request->new_pin_confirmation) {
            return $this->errorResponse('Konfirmasi PIN baru tidak cocok.', null, 422);
        }

        /** @var \App\Models\MobileAccess $mobileAccess */
        $mobileAccess = $request->attributes->get('mobile_access');

        // Verifikasi PIN lama tanpa increment wrong_pin_count (bypass lockout untuk ganti PIN)
        if (! \Illuminate\Support\Facades\Hash::check($request->old_pin, $mobileAccess->pin_hash)) {
            return $this->errorResponse('PIN lama tidak sesuai.', null, 422);
        }

        $mobileAccess->setPin($request->new_pin);
        $mobileAccess->save();

        return response()->json([
            'success' => true,
            'message' => 'PIN berhasil diubah.',
        ]);
    }

    // ─────────────────────────────────────────────────
    //  POST /api/mobile/pin/reset  (Admin/Internal only)
    // ─────────────────────────────────────────────────
    /**
     * Reset blokir PIN dan set PIN baru — dipanggil oleh teller/admin.
     * Endpoint ini sebaiknya dilindungi middleware tambahan di produksi.
     */
    public function reset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cif_no'  => 'required|string|exists:mobile_access,cif_no',
            'new_pin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', $validator->errors(), 422);
        }

        $mobileAccess = MobileAccess::where('cif_no', $request->cif_no)->firstOrFail();
        $mobileAccess->setPin($request->new_pin);
        $mobileAccess->resetPinLock();

        return response()->json([
            'success' => true,
            'message' => 'PIN berhasil direset. Akun nasabah telah diaktifkan kembali.',
        ]);
    }

    private function errorResponse(string $message, mixed $errors = null, int $status = 400): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $status);
    }
}
