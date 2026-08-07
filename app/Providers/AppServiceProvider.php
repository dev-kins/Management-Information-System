<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

// Models
use App\Models\Announcement;
use App\Models\Student;
use App\Models\Professor;
use App\Models\Grade;
use App\Models\Enrollment;
use App\Models\ReportCardRequest;


// Observer
use App\Observers\AuditTrailObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Dynamically set session lifetime (in minutes) from settings
    try {
    $timeout = (int) setting('security_session_timeout', 60);
    Config::set('session.lifetime', $timeout);
} catch (\Throwable $e) {
    Config::set('session.lifetime', 60);
}

        // Attach Observer to multiple models
        Student::observe(AuditTrailObserver::class);
        Professor::observe(AuditTrailObserver::class);
        Announcement::observe(AuditTrailObserver::class);
        Grade::observe(AuditTrailObserver::class);
        Enrollment::observe(AuditTrailObserver::class);
        ReportCardRequest::observe(AuditTrailObserver::class);

        // Global unread announcement counter
        View::composer('*', function ($view) {
            $user = Auth::user();
            $unreadCount = 0;

            if ($user) {
                $lastLogin = $user->last_login_at ?? now()->subYear();
                $unreadCount = Announcement::where('created_at', '>', $lastLogin)->count();
            }

            $view->with('unreadCount', $unreadCount);
        });
    }
}
