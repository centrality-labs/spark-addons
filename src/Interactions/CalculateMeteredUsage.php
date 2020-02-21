<?php

namespace CentralityLabs\SparkAddons\Interactions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use CentralityLabs\SparkAddons\Contracts\Interactions\CalculateMeteredUsage as Contract;

class CalculateMeteredUsage implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($addonSubscription)
    {
        // Load the subscription
        $subscription = $addonSubscription->subscription;
        // Timestamp when the add-on was created
        $createdAt = $addonSubscription->created_at;
        // Fetch the timestamp for the start of the current stripe billing cycle
        $rememberMinutes = now()->addWeek(config('spark-addons.weeksToCacheBillingCycleTimestamps'))->diffInMinutes();
        $cacheKey = 'subscription.'. $addonSubscription->subscription_id . '.stripe.current_period_start';
        $currentPeriodStart = Cache::remember($cacheKey, $rememberMinutes, function () use ($subscription) {
                $time = $subscription
                    ->asStripeSubscription()
                    ->current_period_start;
                return Carbon::createFromTimestampUTC($time);
            }
        );
        // Calculate the hour based usage for the add-on subscription. If the add-on was created
        // before the start of the current billing cycle, use the `current_period_start` timestamp
        // to calculate the hours accumulated. If the add-on was created after the start of the
        // current billing cycle, use the `created_at` timestamp to calculate the total hours.
        $start = $createdAt->greaterThan($currentPeriodStart) ? $createdAt : $currentPeriodStart;
        // Calculate the hours between current period start and now (or when it ended)
        return (int) ceil($start->diffInMinutes($addonSubscription->ends_at ?: null) / Carbon::MINUTES_PER_HOUR);
    }
}
