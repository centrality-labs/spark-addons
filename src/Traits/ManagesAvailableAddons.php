<?php

namespace CentralityLabs\SparkAddons\Traits;

use Spark;
use Carbon\Carbon;
use CentralityLabs\SparkAddons\Addon;
use Illuminate\Support\Facades\Cache;
use CentralityLabs\SparkAddons\Contracts\Interactions\CalculateMeteredUsage;

trait ManagesAvailableAddons
{
    /**
     * The available add-ons.
     *
     * @var array
     */
    public static $addons = [];
    /**
     *
     * Create a new add-on instance.
     *
     * @param  Addon  $addon
     * @return void
     */
    public static function addon($addon)
    {
        static::$addons[] = $addon;
    }
    /**
     * Get the available add-ons
     *
     * @return \Illuminate\Support\Collection
     */
    public static function addons()
    {
        return collect(static::$addons);
    }
    /**
     * Find a specific add-on by the ID.
     *
     * @param  string  $id
     * @return mixed
     */
    public static function findAddonById($id)
    {
        return static::addons()->where('id', $id)->first();
    }
    /**
     * Get all of the add-on subscriptions.
     *
     * @param $model
     * @return \Illuminate\Support\Collection
     */
    public static function addonSubscriptionsToBeSettled($model)
    {
        return $model->addonSubscriptions()
            ->with('subscription')
            ->get()
            ->map(function($addonSubscription) {
                $subscription = $addonSubscription->subscription;
                // Fetch the timestamp for the start of the current stripe billing cycle
                $rememberMinutes = now()->addWeek(config('spark-addons.weeksToCacheBillingCycleTimestamps'))->diffInMinutes();
                $cacheKey = "subscription.{$addonSubscription->subscription_id}.stripe.current_period_start";
                $currentPeriodStart = Cache::remember($cacheKey, $rememberMinutes, function () use ($subscription) {
                    $time = $subscription
                        ->asStripeSubscription()
                        ->current_period_start;
                    return Carbon::createFromTimestampUTC($time);
                });
                $addonSubscription->subscription->current_period_start = $currentPeriodStart;
                $addonSubscription->addon = static::findAddonById($subscription->addon_id);
                $addonSubscription->addonPlan = static::findAddonPlanById($subscription->provider_plan);
                return $addonSubscription;
            })->reject(function($addonSubscription) {
                // Reject add-on subscriptions that have already been settled
                if($addonSubscription->ends_at) {
                    return $addonSubscription->ends_at->lessThan($addonSubscription->subscription->current_period_start);
                } else {
                    return false;
                }
            });
    }
}
