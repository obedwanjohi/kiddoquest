<?php

/*
|--------------------------------------------------------------------------
| KiddoQuest app platform configuration
|--------------------------------------------------------------------------
| Everything the Flutter app (phone / tablet / Android TV) needs from the
| backend, in one place. GET /api/v1/config exposes the client-facing half
| of this file, so changing a value here changes it for every installed app
| on the next config fetch — no app release required.
|
| See KIDDOQUEST_FLUTTER_APP_PLAN.md §7.8.
*/

return [

    /*
    | The minimum app version allowed to talk to this API. Older builds get a
    | forced-update screen. Bump only for breaking contract changes.
    */
    'min_app_version' => env('APP_MIN_VERSION', '1.0.0'),

    /*
    | Local dates (streaks, daily stats, screen time) are computed in this zone
    | when a client sends a timestamp without a usable offset.
    */
    'timezone' => env('APP_LOCAL_TIMEZONE', 'Africa/Nairobi'),

    /*
    |--------------------------------------------------------------------------
    | Content packs
    |--------------------------------------------------------------------------
    | A pack is one adventure world at one version: pack.json plus the media it
    | references. Packs are written to a filesystem disk; in production point
    | `disk` at S3/R2 and `cdn_url` at the CDN in front of it.
    */
    'content' => [
        'disk'      => env('CONTENT_DISK', 'public'),
        'root'      => env('CONTENT_ROOT', 'packs'),
        'cdn_url'   => env('CONTENT_CDN_URL'),          // null => the disk's own URL
        'keep_versions' => (int) env('CONTENT_KEEP_VERSIONS', 3),

        // Worlds that are playable without a subscription. The first world of
        // every subject is free as well (see EnsureActiveSubscription).
        'free_world_slugs' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mission sessions
    |--------------------------------------------------------------------------
    | How many questions a child is asked per attempt, per level code. The web
    | database mostly left this at 10, which is too long for a three-year-old;
    | the handover document mandates 5-6 for Play Group and 8 for PP1/PP2.
    */
    'session' => [
        'questions_per_level' => [
            'PG'  => 6,
            'PP1' => 8,
            'PP2' => 8,
        ],
        'questions_default'     => 8,
        'pass_threshold_percent'=> 60,
        'exclusion_window_days' => 7,
        'max_time_spent_seconds'=> 7200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reward economy (must match the Dart implementation in mobile/)
    |--------------------------------------------------------------------------
    */
    'rewards' => [
        'star_thresholds' => ['three' => 90, 'two' => 60, 'one' => 30],
        'coins' => [
            'base'            => 10,
            'per_stars'       => [3 => 15, 2 => 5],
            'streak_first_day'=> 5,
            'streak_continued'=> 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync ingestion
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'max_events_per_request' => (int) env('SYNC_MAX_EVENTS', 200),
        'future_skew_hours'      => 24,
        'past_skew_days'         => 90,
        'rate_limit_per_minute'  => (int) env('SYNC_RATE_LIMIT', 60),
        'queue'                  => env('SYNC_QUEUE', 'sync-projection'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Entitlement
    |--------------------------------------------------------------------------
    | Days a paid world keeps working offline after a subscription expires.
    */
    'entitlement' => [
        'offline_grace_days' => (int) env('SUBSCRIPTION_GRACE_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature flags shipped to the app
    |--------------------------------------------------------------------------
    */
    'features' => [
        'tv_speak_recognition' => (bool) env('FEATURE_TV_STT', true),
        'treasure_chests'      => (bool) env('FEATURE_TREASURE', false),
        'ai_coach'             => (bool) env('FEATURE_AI_COACH', true),
        'songs_hub'            => (bool) env('FEATURE_SONGS', true),
        'devotional'           => (bool) env('FEATURE_DEVOTIONAL', true),
        // Sequential unlock within a world; all worlds stay visible.
        'sequential_unlock'    => (bool) env('FEATURE_SEQUENTIAL_UNLOCK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shop catalogue
    |--------------------------------------------------------------------------
    | The server's copy of what things cost, so an offline purchase can be
    | checked against a price the device does not get to choose. Ids and prices
    | match KidShopController.
    */
    'shop' => [
        'hats' => [
            'hat_star'       => 0,
            'hat_party'      => 0,
            'hat_pirate'     => 30,
            'hat_sunglasses' => 40,
            'hat_crown'      => 50,
            'hat_superhero'  => 60,
            'hat_dino'       => 80,
            'hat_astronaut'  => 100,
        ],
        'characters' => [
            'char_panda'   => 0,
            'char_unicorn' => 0,
            'char_koala'   => 15,
            'char_dino'    => 20,
            'char_robot'   => 25,
            'char_dragon'  => 30,
        ],
    ],

    'maintenance_banner' => env('APP_MAINTENANCE_BANNER'),

    /*
    |--------------------------------------------------------------------------
    | TV device-code login
    |--------------------------------------------------------------------------
    */
    'device_code' => [
        'ttl_minutes'  => 10,
        'code_length'  => 6,
        'poll_seconds' => 3,
    ],

];
