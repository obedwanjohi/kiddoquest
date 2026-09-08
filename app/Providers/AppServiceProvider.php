<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiters();

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
        if (app()->environment('production') || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        if (app()->environment('local') && request()) {
            $scheme = request()->header('X-Forwarded-Proto', request()->getScheme());
            $host = request()->getHttpHost(); // includes port
            if ($host) {
                URL::forceRootUrl("{$scheme}://{$host}");
            }
        }
    }

    /**
     * Named limiters for the app API.
     *
     * Sync is limited per device rather than per IP: a school or a home with one
     * router would otherwise throttle every child behind it at once.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('sync', function (Request $request) {
            $perMinute = (int) config('kiddoquest.sync.rate_limit_per_minute', 60);
            $key = $request->header('X-Device-Id') ?: ($request->user()?->id ?: $request->ip());

            return Limit::perMinute($perMinute)->by('sync:' . $key);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
