<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface SubscribeAddon
{
    /**
     * Get a validator instance for the given data.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $data
     * @return \Illuminate\Validation\Validator
     */
    public function validator($user, array $data);
    /**
     * Adds a new add-on subscription to a owner
     *
     * @param mixed $owner
     * @param \CentralityLabs\SparkAddons\AddonPlan $plan
     * @param array $data
     * @return void
     */
    public function handle($owner, $plan, array $data);
}
