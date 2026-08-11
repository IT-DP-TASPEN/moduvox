<?php

use Carbon\Carbon;

if (!function_exists('formatRupiah')) {
    /**
     * Format number to Indonesian Rupiah.
     *
     * @param float|int $amount
     * @param bool $withSuffix
     * @return string
     */
    function formatRupiah($amount, $withSuffix = false) {
        $formatted = 'Rp ' . number_format($amount, 0, ',', '.');
        return $withSuffix ? $formatted . ',-' : $formatted;
    }
}

if (!function_exists('formatTanggal')) {
    /**
     * Format date to Indonesian style.
     * Default: 10 Mei 2026
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    function formatTanggal($date, $format = 'D MMMM YYYY') {
        if (!$date) return '-';
        return Carbon::parse($date)->isoFormat($format);
    }
}

if (!function_exists('formatAngka')) {
    /**
     * Format number with Indonesian thousand separator.
     *
     * @param float|int $number
     * @param int $decimal
     * @return string
     */
    function formatAngka($number, $decimal = 0) {
        return number_format($number, $decimal, ',', '.');
    }
}
