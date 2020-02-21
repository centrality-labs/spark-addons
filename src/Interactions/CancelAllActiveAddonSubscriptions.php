<?php

namespace CentralityLabs\SparkAddons\Interactions;

use Spark;
use CentralityLabs\SparkAddons\Contracts\Interactions\RecordUsage;
use CentralityLabs\SparkAddons\Contracts\Interactions\CalculateMeteredUsage;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelAddonSubscription;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelAllActiveAddonSubscriptions as Contract;

class CancelAllActiveAddonSubscriptions implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($owner, $recordUsage = false)
    {
        if(!method_exists($owner, 'addonSubscriptions')) {
            return;
        }
        $owner->addonSubscriptions()->active()->get()->map(function ($addonSubscription) use ($recordUsage) {
            // Find the add-on plan
            $addonPlan = Spark::findAddonPlanById($addonSubscription->subscription->provider_plan);
            // Record usage
            if($recordUsage && $addonPlan->usageType == "metered") {
                $usage = Spark::call(CalculateMeteredUsage::class, [$addonSubscription]);
                Spark::call(RecordUsage::class, [$addonSubscription, $usage]);
            }
            // Cancel addon subscription
            Spark::call(CancelAddonSubscription::class, [$addonSubscription, $addonPlan]);
            // Cancel the add-on
            if(!empty($addonPlan->onCancel)) {
                dispatch(new $addonPlan->onCancel($addonSubscription->owner, $addonSubscription));
            }
        });
    }
}
