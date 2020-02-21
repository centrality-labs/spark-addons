<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface CancelAddonSubscription
{
    /**
     * Cancels (or reduces) the add-on subscription.
     *
     * @param \CentralityLabs\SparkAddons\AddonSubscription $addonSubscription
     * @param \CentralityLabs\SparkAddons\AddonPlan $addonPlan
     * @return void
     */
    public function handle($addonSubscription, $addonPlan);
}
