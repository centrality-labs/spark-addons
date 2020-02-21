<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface ResumeAddonSubscription
{
    /**
     * Resumes the add-on subscription in the grace period.
     *
     * @param \CentralityLabs\SparkAddons\AddonSubscription $addonSubscription
     * @param \CentralityLabs\SparkAddons\AddonPlan $addonPlan
     * @return void
     */
    public function handle($addonSubscription, $addonPlan);
}
