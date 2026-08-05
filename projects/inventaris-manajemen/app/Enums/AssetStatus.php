<?php

namespace App\Enums;

enum AssetStatus: string
{
    case AKTIF = 'AKTIF';
    case DIHAPUS = 'DIHAPUS';
    case MUTASI_PENDING = 'MUTASI_PENDING';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::DIHAPUS => 'Dihapus / Write-Off',
            self::MUTASI_PENDING => 'Mutasi Pending',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AKTIF => 'green',
            self::DIHAPUS => 'red',
            self::MUTASI_PENDING => 'yellow',
        };
    }
}
