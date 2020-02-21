<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface RecordUsage
{
    /**
     * Record the add-on usage.
     *
     * @param \CentralityLabs\SparkAddons\AddonSubscription $addonSubscription
     * @param int $quantity
     * @return void
     */
    public function handle($addonSubscription, $quantity = 0);
}
