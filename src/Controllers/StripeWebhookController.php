<?php

namespace CentralityLabs\SparkAddons\Controllers;

use Spark;
use Symfony\Component\HttpFoundation\Response;
use CentralityLabs\SparkAddons\Contracts\Interactions\RecordUsage;
use CentralityLabs\SparkAddons\Contracts\Interactions\CalculateMeteredUsage;

class StripeWebhookController extends \Laravel\Spark\Http\Controllers\Settings\Billing\StripeWebhookController
{
    /**
     * Handle a invoice created webhook which Stripe fires approximately one
     * hour before attempting to finalize the invoice and collect payment.
     *
     * @param  array  $payload
     * @return Response
     */
    public function handleInvoiceCreated(array $payload)
    {
        $user = $this->getUserByStripeId(
            $payload['data']['object']['customer']
        );
        if (is_null($user)) {
            return $this->teamInvoiceCreated($payload);
        }
        // Calculate and record usage for metered plans
        $this->calculateAndRecordUsage($user, $payload['data']['object']['lines']['data']);
        return new Response('Webhook Handled', 200);
    }
    /**
     * Handle a invoice created webhook from a Stripe.
     *
     * @param  array  $payload
     * @return Response
     */
    public function teamInvoiceCreated(array $payload)
    {
        $team = Spark::team()->where(
            'stripe_id', $payload['data']['object']['customer']
        )->first();
        if (is_null($team)) {
            return;
        }
        // Calculate usage
        $this->calculateAndRecordUsage($team, $payload['data']['object']['lines']['data']);
        return new Response('Webhook Handled', 200);
    }
    /**
     * Loop through the data and record usage for metered plans
     *
     * @param $owner
     * @param $data
     */
    private function calculateAndRecordUsage($owner, $data)
    {
        $addonSubscriptionsToBeSettled = Spark::addonSubscriptionsToBeSettled($owner);
        collect($data)->reject(function($item) {
            return $item['plan']['usage_type'] != "metered";
        })->each(function($item) use ($owner, $addonSubscriptionsToBeSettled) {
            $addonPlan = Spark::findAddonPlanById($item['plan']['id']);
            if($addonPlan->usageType != "metered") {
                return;
            }
            $subscription = $owner->allSubscriptions()
                ->where('stripe_item_id', $item['subscription_item'])
                ->first();
            $addonSubscriptions = $addonSubscriptionsToBeSettled
                ->where('subscription_id', $subscription->id)
                ->all();
            collect($addonSubscriptions)->each(function($addonSubscription) use ($addonPlan) {
                if($addonPlan->usageType == "metered") {
                    $usage = Spark::call(CalculateMeteredUsage::class, [$addonSubscription]);
                    Spark::call(RecordUsage::class, [$addonSubscription, $usage]);
                }
            });
        });
    }
}
