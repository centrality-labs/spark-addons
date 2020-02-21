<?php

namespace CentralityLabs\SparkAddons\Interactions;

use CentralityLabs\SparkAddons\Contracts\Interactions\RecordUsage as Contract;

class RecordUsageUsingStripe implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($addonSubscription, $quantity = 0)
    {
        $subscription = $addonSubscription->subscription;
        // Record usage on Stripe
        \Stripe\UsageRecord::create([
            "action" => "increment",
            "quantity" => $quantity,
            "timestamp" => now('utc')->getTimestamp(),
            "subscription_item" => $subscription->stripe_item_id
        ], ['api_key' => $subscription->owner->getStripeKey()]);
        // Update usage quantity locally
        $subscription = $addonSubscription->subscription->fresh();
        $subscription->quantity = $subscription->quantity + $quantity;
        $subscription->save();
    }
}
