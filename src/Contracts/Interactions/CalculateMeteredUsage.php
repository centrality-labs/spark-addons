<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface CalculateMeteredUsage
{
    /**
     * Calculates the current metered usage amount of an add-on.
     *
     * @param \CentralityLabs\SparkAddons\AddonSubscription $addonSubscription
     * @return int
     */
    public function handle($addonSubscription);
}
