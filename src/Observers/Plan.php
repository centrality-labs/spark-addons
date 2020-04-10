<?php

namespace CentralityLabs\SparkAddons\Observers;

use Laravel\Cashier\Cashier;
use Stripe\Error\InvalidRequest;

/**
 * Class PlanObserver
 * @package App\Observers
 */
class PlanObserver
{
    /**
     * @param PlanContract $plan
     */
    public function created (Plan $plan)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            \Stripe\Plan::create([
                'id'                => $plan->id,
                'amount'            => $plan->price * 100,
                'interval'          => Str::intervalToUnit($plan->interval),
                'name'              => $plan->name,
                'currency'          => Cashier::usesCurrency(),
                'active'            => $plan->active,
                'trial_period_days' => $plan->trail_days
            ]);
        } catch( InvalidRequest $e) {
            // let's suppose it already exists
        }
    }

    /**
     * @param PlanContract $plan
     */
    public function updated (Plan $plan)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Plan::update($plan->provider_id, [
            'amount' => $plan->price * 100,
            'interval' => ($plan->period == PlanContract::PERIOD_MONTHLY ? PlanContract::STRIPE_PERIOD_MONTHLY : PlanContract::STRIPE_PERIOD_YEARLY),
            'name' => $plan->name,
            'currency' => Cashier::usesCurrency(),
            'active'            => $plan->active,
            'trial_period_days' => $plan->trail_days
        ]);
    }

    /**
     * @param PlanContract $plan
     */
    public function deleting (plan $plan)
    {
        // Dont' delete it, keep it for archiving
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Plan::update($plan->provider_id, [
            'active'            => false
        ]);
    }
}