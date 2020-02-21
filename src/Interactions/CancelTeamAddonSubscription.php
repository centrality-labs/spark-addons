<?php

namespace CentralityLabs\SparkAddons\Interactions;

use Spark;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelAddonSubscription;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelTeamAddonSubscription as Contract;

class CancelTeamAddonSubscription implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($addonSubscription, $addonPlan)
    {
        return Spark::call(CancelAddonSubscription::class, [
            $addonSubscription, $addonPlan
        ]);
    }
}
