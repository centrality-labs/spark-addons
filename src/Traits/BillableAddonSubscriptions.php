<?php

namespace CentralityLabs\SparkAddons\Traits;

use Spark;

trait BillableAddonSubscriptions
{
    /**
     * Get a subscription instance by name.
     *
     * @param  string  $subscription
     * @return \Laravel\Cashier\Subscription|null
     */
    public function subscription($subscription = 'default')
    {
        return $this->allSubscriptions()
            ->orderByRaw('addon_id ASC, created_at DESC')
            ->get()
            ->first(function ($value) use ($subscription) {
                return $value->name === $subscription;
            });
    }
    /**
     * Get all of the subscriptions for the owner including the add-on subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allSubscriptions()
    {
        if(Spark::canBillTeams()) {
            $subscriptionClass = config('spark-addons.subscriptionModels.team');
        } else {
            $subscriptionClass = config('spark-addons.subscriptionModels.user');
        }
        return $this->hasMany($subscriptionClass, $this->getForeignKey());
    }
    /**
     * Get all of the subscriptions for the team.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function subscriptions()
    {
        return $this->allSubscriptions()
            ->whereNull('addon_id')
            ->orderBy('created_at', 'desc');
    }
    /**
     * Get all of the add-on subscriptions for the team.
     *
     * @return \Illuminate\Support\Collection
     */
    public function addonSubscriptions()
    {
        return $this->morphMany(config('spark-addons.subscriptionModels.addon'), 'owner');
    }
    /**
     * Begin creating a new subscription.
     * We have to override the newSubscription method
     *
     * @param  string  $subscription
     * @param  string  $plan
     * @return \Laravel\Cashier\SubscriptionBuilder
     */
    public function newSubscription($subscription, $plan)
    {
        return new \CentralityLabs\SparkAddons\MultiPlanSubscriptionBuilder($this, $subscription, $plan);
    }
}
