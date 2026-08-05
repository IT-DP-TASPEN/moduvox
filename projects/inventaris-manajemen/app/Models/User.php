<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'kantor_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Relasi ke kantor/cabang.
     */
    public function kantor(): BelongsTo
    {
        return $this->belongsTo(MstKantor::class, 'kantor_id');
    }

    /**
     * Cek apakah user adalah Kantor Pusat.
     */
    public function isHeadOffice(): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        if ($this->kantor) {
            return in_array($this->kantor->kode, ['00', '01']);
        }

        return in_array($this->kantor_id, ['00', '01', null]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
