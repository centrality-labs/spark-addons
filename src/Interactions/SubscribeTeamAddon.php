<?php

namespace CentralityLabs\SparkAddons\Interactions;

use Spark;
use CentralityLabs\SparkAddons\Contracts\Interactions\SubscribeAddon;
use CentralityLabs\SparkAddons\Contracts\Interactions\SubscribeTeamAddon as Contract;

class SubscribeTeamAddon implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function validator($team, array $data)
    {
        return Spark::call(SubscribeAddon::class.'@validator', [
            $team, $data
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function handle($team, $plan, array $data)
    {
        return Spark::call(SubscribeAddon::class, [
            $team, $plan, $data
        ]);
    }
}
