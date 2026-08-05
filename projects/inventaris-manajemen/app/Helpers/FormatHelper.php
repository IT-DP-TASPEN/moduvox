<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format angka ke Rupiah.
     */
    public static function rupiah(float|int $amount, bool $withPrefix = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');
        return $withPrefix ? "Rp {$formatted}" : $formatted;
    }

    /**
     * Format tanggal ke tampilan Indonesia (dd/mm/yyyy).
     */
    public static function tanggal(?string $date): string
    {
        if (!$date) {
            return '-';
        }

        return \Carbon\Carbon::parse($date)->format('d/m/Y');
    }

    /**
     * Generate kode referensi jurnal.
     * Format: IV-{KodeCabang2}{YY}{MM}{Counter3}
     */
    public static function generateJournalRef(string $kodeCabang, string $periode, int $counter): string
    {
        $cabang = str_pad($kodeCabang, 2, '0', STR_PAD_LEFT);
        $seq = str_pad($counter, 3, '0', STR_PAD_LEFT);

        return "IV-{$cabang}{$periode}{$seq}";
    }

    /**
     * Pad kode menjadi 2 digit (misal: '1' -> '01').
     */
    public static function padCode(string|int $code, int $length = 2): string
    {
        return str_pad($code, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Format Carbon date ke tampilan Indonesia (misal: "30 Juni 2026").
     */
    public static function tanggalIndonesia($date): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $carbon->day . ' ' . ($bulan[$carbon->month] ?? '?') . ' ' . $carbon->year;
    }
}
