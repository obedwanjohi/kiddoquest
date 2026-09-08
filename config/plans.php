<?php

/*
|--------------------------------------------------------------------------
| Subscription plans & parent-zone defaults — the single source of truth
|--------------------------------------------------------------------------
| The landing page, the M-Pesa checkout, MpesaService and the subscription
| middleware all read from here. Change a price once, everywhere follows.
*/

return [

    'currency' => env('PLAN_CURRENCY', 'KES'),

    // When false (default) every world is playable. Flip to true to require an
    // active subscription for all but the free worlds below.
    'enforce_subscription' => (bool) env('SUBSCRIPTION_ENFORCE', false),

    // Days a paid subscription keeps working after expiry (used by the app's offline mode).
    'offline_grace_days' => (int) env('SUBSCRIPTION_GRACE_DAYS', 7),

    // The first world (lowest sort_order) of every subject is always free. Add slugs here
    // to make additional worlds free, e.g. 'line-tracing-trail', 'speak-repeat-safari'.
    'free_world_slugs' => [],

    // Starter PIN given to new parent accounts (stored hashed; parents are nagged to change it).
    'default_parent_pin' => (string) env('PARENT_DEFAULT_PIN', '1234'),

    // Seeder controls (see database/seeders/*).
    'seed_demo_accounts' => (bool) env('SEED_DEMO_ACCOUNTS', false),
    'admin_seed' => [
        'email'    => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    | Plans. Keys are the plan_type stored on subscriptions.
    |
    | KES 200 a month and 1,800 a year, confirmed by the owner on 8 September
    | 2026. The old checkout charged 499 / 1,200 / 3,999; that list is dead and
    | must not be reintroduced.
    |
    | This is the only place a price is written. The landing page, the web
    | checkout, MpesaService, the subscription middleware and the app's payment
    | screen all read from here — the app holds no price of its own, not even a
    | fallback, so changing a number here changes it everywhere with no release.
    |
    | (The `amount` columns on subscriptions and payments still carry an old
    | 499.00 database default. It is never reached: every write sets the amount
    | explicitly from this file.)
    */
    'plans' => [
        'monthly' => [
            'name'      => 'Monthly Quest',
            'emoji'     => '💳',
            'blurb'     => 'Month-to-month flexibility',
            'amount'    => (int) env('PLAN_MONTHLY_KES', 200),
            'days'      => 30,
            'badge'     => null,
            'highlight' => false,
        ],
        'annual' => [
            'name'      => 'Annual Champion',
            'emoji'     => '🏆',
            'blurb'     => 'Full year of uninterrupted mastery',
            'amount'    => (int) env('PLAN_ANNUAL_KES', 1800),
            'days'      => 365,
            'badge'     => 'Save 25%',
            'highlight' => true,
        ],
        // 'termly' => ['name' => 'School Term', 'emoji' => '🏫', 'blurb' => '90 days access',
        //              'amount' => 540, 'days' => 90, 'badge' => 'Save 10%', 'highlight' => false],
    ],

];
