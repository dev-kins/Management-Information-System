<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        static $settings = null;

        if (!Schema::hasTable('settings')) {
            return $default;
        }

        if ($settings === null) {
            $settings = Setting::query()
                ->pluck('value', 'key')
                ->all();
        }

        return $settings[$key] ?? $default;
    }
}
