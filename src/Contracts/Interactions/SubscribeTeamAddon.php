<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface SubscribeTeamAddon
{
    /**
     * Get a validator instance for the given data.
     *
     * @param  App\Team  $team
     * @param  array  $data
     * @return \Illuminate\Validation\Validator
     */
    public function validator($team, array $data);
    /**
     * Adds a new add-on subscription to a team
     *
     * @param \App\Team $team
     * @param \CentralityLabs\SparkAddons\AddonPlan $plan
     * @param array $data
     * @return void
     */
    public function handle($team, $plan, array $data);
}
