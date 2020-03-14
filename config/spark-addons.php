<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Here you can specify the routes for Spark Addons to use.
    |
    */
    'routes' => [
        'addons' => 'addons',
        'plans' => 'plans',
        'subscriptions' => 'subscriptions',
        'subscribe' => 'subscribe',
        'cancel' => 'cancel',
        'api' => 'api',
    ],
    /*
    |--------------------------------------------------------------------------
    | Identify teams by path
    |--------------------------------------------------------------------------
    |
    | When enabled in combination with `Spark::identifyTeamsByPath()`,
    | your app will use the slug to determine the team being used.
    |
    */
    'teamsIdentifiedByPath' => false,
    /*
    |--------------------------------------------------------------------------
    | Team index key
    |--------------------------------------------------------------------------
    |
    | Change the key used to find a team based. When `teamsIdentifiedByPath`
    | is enabled, change this value to `slug` to switch so your app uses
    | the slug in the path as the index to search on to find the team.
    |
    */
    'teamsIndexKey' => 'id',
    /*
    |--------------------------------------------------------------------------
    | Subscription Models
    |--------------------------------------------------------------------------
    |
    | Specify custom subscription models for Spark Addons to use.
    |
    */
    'subscriptionModels'  => [
        'user' => 'CentralityLabs\SparkAddons\Subscription',
        'team' => 'CentralityLabs\SparkAddons\TeamSubscription',
        'addon' => 'CentralityLabs\SparkAddons\AddonSubscription',
    ],
    /*
    |--------------------------------------------------------------------------
    | Weeks to Cache Billing Cycle Timestamps
    |--------------------------------------------------------------------------
    |
    | The number of weeks to cache the billing cycle timestamps fetched
    | from Stripe. This value should be close to your billing cycle.
    |
    */
    'weeksToCacheBillingCycleTimestamps' => 2,
];
