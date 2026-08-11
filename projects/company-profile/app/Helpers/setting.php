<?php

if (!function_exists('setting')) {
    function setting($page, $key, $default = null) {
        $cacheKey = "pagesetting:{$page}:{$key}";

        $value = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($page, $key) {
            $setting = \App\Models\PageSetting::where('page', $page)->where('key', $key)->first();
            return $setting ? $setting->value : null;
        });

        return $value !== null ? $value : $default;
    }
}
