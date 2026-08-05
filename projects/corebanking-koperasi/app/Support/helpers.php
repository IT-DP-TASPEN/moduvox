<?php

if (! function_exists('format_percent')) {
    function format_percent(float|int|string|null $value, int $decimals = 2, bool $withSymbol = true): string
    {
        $formatted = number_format((float) ($value ?? 0), $decimals, ',', '.');

        return $withSymbol ? $formatted . '%' : $formatted;
    }
}
