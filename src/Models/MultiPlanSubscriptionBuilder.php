<?php

namespace CentralityLabs\SparkAddons;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionBuilder;
/**
 * This class lets you build multi plan subscriptions.
 *
 * Cashier's default SubscriptionBuilder is not designed
 * to work with multi plan subscriptions. This new class will
 * introduce the additional functionality required to support them,
 * without requiring refactoring of existing Cashier implementations.
 */
class MultiPlanSubscriptionBuilder extends SubscriptionBuilder
{
    /**
     * Configures how the quantity per period should be determined
     *
     * @var string
     */
    public $usageType = 'licensed';
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
     * Create a new Stripe multi plan subscription.
     *
     *
     * @param  string|null  $token
     * @param  array  $options
     * @return \Laravel\Cashier\Subscription
     */
    public function create($token = null, array $options = [])
    {
        $customer = $this->getStripeCustomer($token, $options);
        $subscriptionItem = $this->createStripeSubscription($customer);
        if ($this->skipTrial) {
            $trialEndsAt = null;
        } else {
            $trialEndsAt = $this->trialExpires;
        }
        $subscription = $this->owner->allSubscriptions()->updateOrCreate(
            [
                'name' => $this->name,
                'stripe_id' => $subscriptionItem->subscription,
                'stripe_plan' => $this->plan,
                'stripe_item_id' => $subscriptionItem->id,
            ],
            [
                'addon_id' => $subscriptionItem->metadata->addon,
                'quantity' => (int) $subscriptionItem->quantity,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => null,
            ]
        );
        return $subscription;
    }
    /**
     * Here we want to check if a subscription is already available. If
     * there's already a subscription available, we want to skip creating
     * a new subscription, we just add a subscriptionItem to the subscription.
     *
     * @param $customer
     * @return \Stripe\SubscriptionItem
     */
    protected function createStripeSubscription($customer)
    {
        if(!$this->owner->subscribed($this->name)) {
            // Create new subscription
            $subscription = $customer->subscriptions->create($this->buildPayload());
            $subscriptionItem = array_get($subscription->items, 'data.0');
            // Cache the current period start for easy access later on
            $this->cacheCurrentPeriodStart($subscription);
        } else {
            // Update an existing subscription
            if(!$this->subscribedWithPlan($this->name, $this->plan)) {
                // Get an existing subscription
                $subscription = $this->owner->subscription($this->name);
                // Create a new subscription item for the subscription
                $subscriptionItem = $this->createStripeSubscriptionItem(
                    $subscription,
                    $this->buildSubscriptionItemPayload($subscription->stripe_id)
                );
            } else {
                // Get the matching subscription
                $subscription = $this->owner->allSubscriptions()
                    ->where('name', $this->name)
                    ->where('stripe_plan', $this->plan)
                    ->firstOrFail();
                // Update an existing subscription item by incrementing quantity for licensed plans
                $subscriptionItem = $this->incrementCurrentStripeSubscriptionItemQuantity(
                    $subscription,
                    $subscription->stripe_item_id
                );
            }
        }
        return $subscriptionItem;
    }
    /**
     * Create a new SubscriptionItem for an existing subscription.
     *
     * @param Subscription $subscription
     * @param array $data
     * @return \Stripe\ApiResource
     */
    protected function createStripeSubscriptionItem($subscription, array $data)
    {
        return \Stripe\SubscriptionItem::create(
            $data, ['api_key' => $subscription->owner->getStripeKey()]
        );
    }
    /**
     * Update a SubscriptionItem by incrementing the quantity by one.
     *
     * @param Subscription $subscription
     * @param int $subscriptionItemId
     * @return \Stripe\StripeObject
     */
    public function incrementCurrentStripeSubscriptionItemQuantity($subscription, $subscriptionItemId, $count = 1)
    {
        $subscriptionItem = \Stripe\SubscriptionItem::retrieve(
            $subscriptionItemId, ['api_key' => $subscription->owner->getStripeKey()]
        );
        // Only increment the quantity for licensed plans. For metered plans,
        // we don't need to do anything since the metered usage is what matters.
        if($this->usageType == "licensed") {
            $subscriptionItem->quantity = $subscriptionItem->quantity + $count;
            $subscriptionItem = $subscriptionItem->save();
        }
        // Update usage quantity locally
        $subscription->quantity = (int) $subscriptionItem->quantity;
        $subscription->save();
        return $subscriptionItem;
    }
    /**
     * Update a SubscriptionItem by decrementing the quantity by one.
     *
     */
    public function decrementCurrentStripeSubscriptionItemQuantity($subscription, $subscriptionItemId, $count = 1)
    {
        $subscriptionItem = \Stripe\SubscriptionItem::retrieve(
            $subscriptionItemId, ['api_key' => $subscription->owner->getStripeKey()]
        );
        // Only decrement the quantity for licensed plans. For metered plans,
        // we don't need to do anything since the metered usage is what matters.
        if($this->usageType == "licensed") {
            $subscriptionItem->quantity = max(0, $subscriptionItem->quantity - $count);
            $subscriptionItem = $subscriptionItem->save();
        }
        // Update usage quantity locally
        $subscription->quantity = (int) $subscriptionItem->quantity;
        $subscription->save();
        return $subscriptionItem;
    }
    /**
     * Cancel a Stripe multi subscription item
     */
    public function cancel($addonSubscription)
    {
        $subscription = $addonSubscription->subscription;
        $this->decrementCurrentStripeSubscriptionItemQuantity(
            $subscription, $subscription->stripe_item_id
        );
    }
    /**
     * Determine the Stripe model has subscription with the corresponding plan.
     *
     * @param string $subscription
     * @param string $plan
     * @return bool
     */
    protected function subscribedWithPlan($subscription, $plan)
    {
        return $this->owner->allSubscriptions()
            ->where('name', $subscription)
            ->where('stripe_plan', $plan)
            ->exists();
    }
    /**
     * Build the payload for multi plan subscription creation.
     *
     * @return array
     */
    protected function buildPayload()
    {
        return array_filter([
            'billing_cycle_anchor' => $this->billingCycleAnchor,
            'coupon' => $this->coupon,
            'metadata' => $this->metadata,
            'items' =>  [$this->buildSubscriptionItemPayload()],
            'tax_percent' => $this->getTaxPercentageForPayload(),
            'trial_end' => $this->getTrialEndForPayload(),
        ]);
    }
    /**
     * Build the payload for a subscription item.
     *
     * @param null $subscription
     * @return array
     */
    protected function buildSubscriptionItemPayload($subscription = null)
    {
        $payload = [
            'plan' => $this->plan,
            'metadata' => $this->metadata,
        ];
        if($this->usageType != "metered") {
            $payload['quantity'] = $this->quantity;
        }
        if($subscription) {
            $payload['subscription'] = $subscription;
        }
        return $payload;
    }
    protected function cacheCurrentPeriodStart($subscription, $cacheRememberWeeks = 2)
    {
        // Fetch the timestamp for the start of the current stripe billing cycle
        $rememberMinutes = now()->addWeek($cacheRememberWeeks)->diffInMinutes();
        $cacheKey = 'subscription.'. $subscription->id . '.stripe.current_period_start';
        Cache::remember($cacheKey, $rememberMinutes, function () use ($subscription) {
            return Carbon::createFromTimestampUTC($subscription->current_period_start);
        });
    }
}
