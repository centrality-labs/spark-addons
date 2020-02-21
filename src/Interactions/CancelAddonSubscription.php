<?php

namespace CentralityLabs\SparkAddons\Interactions;

use Spark;
use Carbon\Carbon;
use CentralityLabs\SparkAddons\Contracts\Interactions\RecordUsage;
use CentralityLabs\SparkAddons\Contracts\Interactions\CalculateMeteredUsage;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelAddonSubscription as Contract;

class CancelAddonSubscription implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($addonSubscription, $addonPlan)
    {
        // Cancel subscription
        $addonSubscription->owner->newSubscription('default', $addonSubscription->subscription->stripe_plan)
            ->usageType($addonPlan->usageType)
            ->cancel($addonSubscription);
        // Cancel metered plans immediately, cancel licensed plans at period end
        if($addonPlan->usageType == "metered") {
            $addonSubscription->fill(['ends_at' => now()])->save();
            // Fire event:
        } else {
            $subscription = $addonSubscription->subscription->asStripeSubscription();
            $addonSubscription->fill([
                'ends_at' => Carbon::createFromTimestamp($subscription->current_period_end)
            ])->save();
        }
    }
}
