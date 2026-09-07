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

    // Plans. Keys are the plan_type stored on subscriptions. Prices are the ones the
    // public landing page advertised (KES 200 / month, KES 1,800 / year); the old
    // checkout charged 499 / 1,200 / 3,999 — adjust here if that was the intended list.
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
