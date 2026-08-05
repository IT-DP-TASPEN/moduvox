<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class MobileAccess extends Model
{
    use SoftDeletes;

    protected $table = 'mobile_access';

    protected $fillable = [
        'cif_id',
        'cif_no',
        'username',
        'password_hash',
        'pin_hash',
        'activated_at',
        'device_id',
        'fcm_token',
        'is_active',
        'wrong_pin_count',
        'pin_blocked_at',
        'remember_token',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password_hash',
        'pin_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'wrong_pin_count' => 'integer',
        'pin_blocked_at' => 'datetime',
        'activated_at'   => 'datetime',
        'last_login_at'  => 'datetime',
    ];

    // ─────────────────────────────────────────────────
    //  Relationships
    // ─────────────────────────────────────────────────

    public function cif(): BelongsTo
    {
        return $this->belongsTo(Cif::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(MobileToken::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─────────────────────────────────────────────────
    //  Password Helpers
    // ─────────────────────────────────────────────────

    /**
     * Set & hash password secara otomatis.
     */
    public function setPassword(string $plainPassword): void
    {
        $this->password_hash = Hash::make($plainPassword);
    }

    /**
     * Verifikasi password plain text terhadap hash yang tersimpan.
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return Hash::check($plainPassword, $this->password_hash);
    }

    // ─────────────────────────────────────────────────
    //  PIN Helpers
    // ─────────────────────────────────────────────────

    /**
     * Set & hash PIN secara otomatis.
     */
    public function setPin(string $plainPin): void
    {
        $this->pin_hash       = Hash::make($plainPin);
        $this->wrong_pin_count = 0;
        $this->pin_blocked_at  = null;
    }

    /**
     * Verifikasi PIN dan terapkan logika lockout:
     * - Jika benar  → reset wrong_pin_count, return true
     * - Jika salah  → increment wrong_pin_count; jika >= 3 → set is_active=false
     */
    public function verifyPin(string $plainPin): bool
    {
        if (Hash::check($plainPin, $this->pin_hash)) {
            $this->wrong_pin_count = 0;
            $this->save();
            return true;
        }

        $this->wrong_pin_count += 1;

        if ($this->wrong_pin_count >= 3) {
            $this->is_active     = false;
            $this->pin_blocked_at = now();
        }

        $this->save();
        return false;
    }

    /**
     * Reset blokir PIN dan aktifkan kembali akses.
     */
    public function resetPinLock(): void
    {
        $this->wrong_pin_count = 0;
        $this->pin_blocked_at  = null;
        $this->is_active       = true;
        $this->save();
    }

    // ─────────────────────────────────────────────────
    //  Token Helpers
    // ─────────────────────────────────────────────────

    /**
     * Buat token baru untuk sesi login.
     */
    public function createToken(?string $deviceId = null, ?string $deviceName = null): MobileToken
    {
        // Hapus token lama untuk device yang sama (opsional)
        if ($deviceId) {
            $this->tokens()->where('device_id', $deviceId)->delete();
        }

        $plainToken = bin2hex(random_bytes(40));

        $token = $this->tokens()->create([
            'token'       => hash('sha256', $plainToken),
            'device_id'   => $deviceId,
            'device_name' => $deviceName,
            'expires_at'  => now()->addDays(30),
        ]);

        $token->plain_token = $plainToken;

        return $token;
    }

    /**
     * Hapus semua token aktif (logout).
     */
    public function revokeAllTokens(): void
    {
        $this->tokens()->delete();
    }
}
