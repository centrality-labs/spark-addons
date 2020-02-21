<?

namespace CentralityLabs\SparkAddons\Interactions;

use Spark;
use Validator;
use CentralityLabs\SparkAddons\Contracts\Interactions\SubscribeAddon as Contract;

class SubscribeAddon implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function validator($user, array $data)
    {
        return Validator::make($data, [
            'stripe_token' => 'nullable',
            'plan' => 'required|in:' . Spark::activeAddonPlanIdList(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function handle($owner, $plan, array $data)
    {
        $subscriptionBuilder = $owner->newSubscription('default', $plan->id)
            ->usageType($plan->usageType)
            ->withMetadata([
                'addon' => $plan->addon,
            ]);
        if ($owner->hasEverSubscribedTo('default', $plan->id)) {
            $subscriptionBuilder->skipTrial();
        } else if ($plan->trialDays > 0) {
            $subscriptionBuilder->trialDays($plan->trialDays);
        }
        if (isset($data['coupon'])) {
            $subscriptionBuilder->withCoupon($data['coupon']);
        }
        $subscription = $subscriptionBuilder->create(array_get($data, 'stripe_token', null));
        return $owner->addonSubscriptions()->create([
            'subscription_id' => $subscription->id,
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
        ]);
    }
}
