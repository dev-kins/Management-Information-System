<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        static $settings = null;

        try {
            // If the database isn't available yet (Docker build, composer install, etc.)
            if (!DB::connection()->getPdo()) {
                return $default;
            }

            if (!Schema::hasTable('settings')) {
                return $default;
            }

            if ($settings === null) {
                $settings = Setting::pluck('value', 'key')->all();
            }

            return $settings[$key] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}