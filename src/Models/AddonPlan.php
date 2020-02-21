<?php

namespace CentralityLabs\SparkAddons;

use Laravel\Spark\Plan;

class AddonPlan extends Plan
{
    /**
     * The plan's add-on.
     *
     * @var string
     */
    public $addon;
    /**
     * Indicate that the plan should be selected by default.
     *
     * @var bool
     */
    public $default = false;
    /**
     * Configures how the quantity per period should be determined
     *
     * @var string
     */
    public $usageType = 'licensed';
    /**
     * Label the unit of consumption for a metered plan
     *
     * @var string
     */
    public $unit = 'hour';
    /**
     * The job to be fired when an add-on subscription is created.
     *
     * @var string
     */
    public $onSubscribe;
    /**
     * The job to be fired when an add-on subscription is resumed.
     *
     * @var string
     */
    public $onResume;
    /**
     * The job to be fired when an add-on subscription is cancelled.
     *
     * @var string
     */
    public $onCancel;
    /**
     * Create a new plan instance.
     */
    public function __construct($addon, $planName, $stripeId)
    {
        $this->id = $stripeId;
        $this->name = $planName;
        $this->addon = $addon;
    }
    /**
     * Get the add-on for this plan.
     *
     * @return Addon
     */
    public function addon()
    {
        return Spark::addon($this->addon);
    }
    /**
     * Indicate that the plan should be selected by default.
     *
     * @return $this
     */
    public function default()
    {
        $this->default = true;
        return $this;
    }
    /**
     * Configures how the quantity per period should be determined.
     *
     * @param $usageType
     * @return $this
     */
    public function usageType($usageType)
    {
        $this->usageType = $usageType;
        return $this;
    }
    /**
     * Label the unit of consumption for a metered plan
     *
     * @param $unit
     * @return $this
     */
    public function unit($unit)
    {
        $this->unit = $unit;
        return $this;
    }
    /**
     * The job to be fired when an add-on subscription occurs.
     *
     * @param  string  $job
     * @return $this
     */
    public function onSubscribe($job)
    {
        $this->onSubscribe = $job;
        return $this;
    }
    /**
     * The job to be fired when an add-on subscription is resumed.
     *
     * @param  string  $job
     * @return $this
     */
    public function onResume($job)
    {
        $this->onResume = $job;
        return $this;
    }
    /**
     * The job to be fired when an add-on subscription is cancelled.
     *
     * @param  string  $job
     * @return $this
     */
    public function onCancel($job)
    {
        $this->onCancel = $job;
        return $this;
    }
    /**
     * Get the array form of the plan for serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'usageType' => $this->usageType,
            'unit' => $this->unit,
            'trialDays' => $this->trialDays,
            'interval' => $this->interval,
            'features' => $this->features,
            'active' => $this->active,
            'default' => $this->default,
            'attributes' => $this->attributes,
            'type' => $this->type,
        ];
    }
}
