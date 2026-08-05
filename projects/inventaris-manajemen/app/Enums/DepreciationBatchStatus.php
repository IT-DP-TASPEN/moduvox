<?php

namespace App\Enums;

enum DepreciationBatchStatus: string
{
    case DRAFT = 'DRAFT';
    case VALIDATED = 'VALIDATED';
    case PREVIEW = 'PREVIEW';
    case APPROVED = 'APPROVED';
    case POSTING = 'POSTING';
    case CLOSED = 'CLOSED';
    case REOPENED = 'REOPENED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::VALIDATED => 'Tervalidasi',
            self::PREVIEW => 'Preview Jurnal',
            self::APPROVED => 'Disetujui Manager',
            self::POSTING => 'Posting Jurnal',
            self::CLOSED => 'Periode Ditutup',
            self::REOPENED => 'Dibuka Kembali',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'amber',
            self::VALIDATED => 'blue',
            self::PREVIEW => 'indigo',
            self::APPROVED => 'teal',
            self::POSTING => 'purple',
            self::CLOSED => 'green',
            self::REOPENED => 'orange',
        };
    }
}
