<?php

namespace CentralityLabs\SparkAddons\Traits;

use Spark;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelTeamAddonSubscription;

trait CashierMultiPlanSubscription
{
    /**
     * Extend the default Cashier swap method to support subscription items.
     *
     * @param  string  $plan
     * @return $this
     */
    public function swap($plan)
    {
        $subscription = $this->asStripeSubscription();
        $subscription->prorate = $this->prorate;
        $subscription->cancel_at_period_end = false;
        if (! is_null($this->billingCycleAnchor)) {
            $subscription->billing_cycle_anchor = $this->billingCycleAnchor;
        }
        // If no specific trial end date has been set, the default behavior should be
        // to maintain the current trial state, whether that is "active" or to run
        // the swap out with the exact number of days left on this current plan.
        if ($this->onTrial()) {
            $subscription->trial_end = $this->trial_ends_at->getTimestamp();
        } else {
            $subscription->trial_end = 'now';
        }
        $subscription->save();
        // Retrieve the Stripe subscription item for this subscription. Instead
        // of updating the subscription, we will update the subscription item
        // to avoid conflicts when multiple plans on the subscription exist.
        $subscriptionItem = collect($subscription->items->data)->firstWhere('plan.id', $this->stripe_plan);
        $subscriptionItem->plan = $plan;
        // Again, if no explicit quantity was set, the default behaviors should be to
        // maintain the current quantity onto the new plan. This is a sensible one
        // that should be the expected behavior for most developers with Stripe.
        if ($this->quantity) {
            $subscriptionItem->quantity = $this->quantity;
        }
        $subscriptionItem->save();
        $this->user->invoice();
        $this->fill([
            'stripe_plan' => $plan,
            'ends_at' => null,
        ])->save();
        return $this;
    }
    /**
     * Extend the default Cashier cancelNow method to also cancel add-on subscriptions.
     *
     * The default `cancelNow` method will immediately cancel the subscription, clearing
     * any pending usage records. We don't want that happening since we might have metered
     * add-ons. Instead we will set the subscription to cancel_at_period_end, giving us time
     * to record the metered usage for their account and have it billed in their final invoice.
     *
     * @return $this
     */
    public function cancelNow()
    {
        return parent::cancel();
    }
}
