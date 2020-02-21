<?php

namespace CentralityLabs\SparkAddons\Interactions;

use Spark;
use Laravel\Cashier\Cashier;
use CentralityLabs\SparkAddons\Contracts\Interactions\RecordUsage as Contract;

/**
 * This implementation is for metered add-ons that need pricing to
 * more than two decimal places. Instead of recording the usage
 * via Stripe's RecordUsage, we add just add an invoice item.
 */
class RecordUsageUsingInvoiceItem implements Contract
{
    const HOURS_PER_MONTH = 730.5;
    /**
     * {@inheritdoc}
     */
    public function handle($addonSubscription, $quantity = 0)
    {
        $subscription = $addonSubscription->subscription;
        $addonPlan = Spark::findAddonPlanById($subscription->provider_plan);
        $addon = Spark::findAddonById($addonPlan->addon);
        $estimatedMonthlyCost = round($addonPlan->price * self::HOURS_PER_MONTH, 2);
        $usageTotal = $addonPlan->price * $quantity;
        // Record usage as invoice item on Stripe
        $subscription->owner->tab(
            $addon->name . " " . $addonPlan->name . " - #" . $addonSubscription->id
            . " @ " . Cashier::usesCurrencySymbol() . $estimatedMonthlyCost . "/mo"
            . " (" . Cashier::usesCurrencySymbol() . round($addonPlan->price, 5). "/hr)",
            intval($usageTotal * 100)
        );
        // Update usage quantity locally
        $subscription = $addonSubscription->subscription->fresh();
        $subscription->quantity = $subscription->quantity + $quantity;
        $subscription->save();
    }
}
