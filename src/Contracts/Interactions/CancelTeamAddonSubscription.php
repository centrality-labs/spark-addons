<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface CancelTeamAddonSubscription
{
    /**
     * Cancels (or reduces) the add-on subscription and
     * records any metered usage for the subscription.
     *
     * @param \CentralityLabs\SparkAddons\AddonSubscription $addonSubscription
     * @param \CentralityLabs\SparkAddons\AddonPlan $addonPlan
     * @return void
     */
    public function handle($addonSubscription, $addonPlan);
}
