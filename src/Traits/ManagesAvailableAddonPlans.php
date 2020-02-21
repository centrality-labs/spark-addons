<?php

namespace CentralityLabs\SparkAddons\Traits;

use CentralityLabs\SparkAddons\AddonPlan;

trait ManagesAvailableAddonPlans
{
    /**
     * All of the add-on plans defined for the application.
     *
     * @var array
     */
    public static $addonPlans = [];
    /**
     * Create a add-on plan instance.
     *
     * @param  string|Addon  $addon
     * @param  string  $planName
     * @param  string  $stripeId
     * @return \CentralityLabs\SparkAddons\AddonPlan
     */
    public static function addonPlan($addon, $planName, $stripeId)
    {
        static::$addonPlans[] = $plan = new AddonPlan($addon, $planName, $stripeId);
        return $plan;
    }
    /**
     * Gets all the add-on plans defined for the application.
     *
     * @param string|null $addon
     * @return \Illuminate\Support\Collection
     */
    public static function allAddonPlans($addon = null)
    {
        $plans = is_null($addon) ? collect(static::$addonPlans) : collect(static::$addonPlans)->where('addon', $addon);
        return $plans->map(function ($plan) {
            $plan->type = 'addonPlan';
            return $plan;
        });
    }
    /**
     * Get the plans defined for an add-on
     *
     * @param  string  $addon
     * @return \Illuminate\Support\Collection
     */
    public static function addonPlans($addon)
    {
        return collect(static::allAddonPlans()->where('addon', $addon)->all());
    }
    /**
     * Get a comma delimited list of active add-on plan IDs.
     *
     * @param string|null $addon
     * @return string
     */
    public static function activeAddonPlanIdList($addon = null)
    {
        return implode(',', static::allAddonPlans($addon)->filter(function ($plan) {
            return $plan->active;
        })->pluck('id')->toArray());
    }
    /**
     * Find a specific add-on plan by the ID.
     *
     * @param  string  $id
     * @return mixed
     */
    public static function findAddonPlanById($id)
    {
        return static::allAddonPlans()->where('id', $id)->first();
    }
}
