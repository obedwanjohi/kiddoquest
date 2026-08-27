<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Morph map: maps short alias names stored in entity_type columns
        // to their fully-qualified model class names.
        // ContentAuditLog::log() stores short names like "Lesson", "Subject", etc.
        Relation::morphMap([
            'Admin'     => \App\Models\Admin::class,
            'Subject'   => \App\Models\Subject::class,
            'Topic'     => \App\Models\Topic::class,
            'Lesson'    => \App\Models\Lesson::class,
            'Quiz'      => \App\Models\Quiz::class,
            'Media'     => \App\Models\Media::class,
            'QuizQuestion'  => \App\Models\QuizQuestion::class,
            'QuestionOption' => \App\Models\QuestionOption::class,
        ]);

        // ✅ MOBILE / LAN / TUNNEL TESTING SUPPORT
        // Force the app URL to match whatever host the browser is using.
        // This makes all generated URLs (routes, assets, form actions) work
        // whether accessed via:
        //   - http://localhost:8081        (desktop)
        //   - http://192.168.98.55:8081    (phone on LAN)
        //   - https://abc123.ngrok.io      (phone via tunnel)
        if (app()->environment('local') && request()) {
            $scheme = request()->header('X-Forwarded-Proto', request()->getScheme());
            $host = request()->getHttpHost(); // includes port
            if ($host) {
                URL::forceRootUrl("{$scheme}://{$host}");
            }
        }
    }
}
