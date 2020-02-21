<?php

namespace CentralityLabs\SparkAddons\Controllers;

use Spark;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use CentralityLabs\SparkAddons\Contracts\Interactions\SubscribeAddon;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelAddonSubscription;
use CentralityLabs\SparkAddons\Contracts\Interactions\ResumeTeamAddonSubscription;

class AddonController extends Controller
{
    /**
     * Show the catalog list
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Spark::addons());
    }
    /**
     * Display the specified resource.
     *
     * @param  string  $addon
     * @return \Illuminate\Http\Response
     */
    public function show($addon)
    {
        return response()->json(Spark::findAddonById($addon));
    }
    /**
     * Show the plans for an add-on.
     *
     * @param  string  $addon
     * @return \Illuminate\Http\Response
     */
    public function plans($addon)
    {
        return response()->json(Spark::addonPlans($addon));
    }
    /**
     * Create the add-on subscription for the team.
     *
     * @param  Request  $request
     * @param  string  $addon
     * @return Response
     */
    public function subscribe(Request $request, $addon)
    {
        $user = $request->user();
        // Validate request
        Spark::call(SubscribeAddon::class.'@validator', [
            $user, $request->all()
        ])->validate();
        // Get the add-on plan
        $addonPlan = Spark::findAddonPlanById($request->plan);
        // Create the subscription
        $subscription = Spark::call(SubscribeAddon::class, [
            $user, $addonPlan, $request->all()
        ]);
        // Launch the add-on
        if(!empty($addonPlan->onSubscribe)) {
            dispatch(new $addonPlan->onSubscribe($user, $subscription, $request->all()));
        }
        return response()->json('OK');
    }
    /**
     * Update the subscription for the team.
     *
     * @param Request $request
     * @param int $addonId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $addonId)
    {
        $user = $request->user();
        // Get the add-on subscription
        $addonSubscription = $user->addonSubscriptions()->findOrFail($addonId);
        // Find the add-on plan
        $addonPlan = Spark::findAddonPlanById($addonSubscription->subscription->provider_plan);
        // Resume
        Spark::call(ResumeTeamAddonSubscription::class, [$addonSubscription, $addonPlan]);
        // Resume the add-on
        if(!empty($addonPlan->onResume)) {
            dispatch(new $addonPlan->onResume($user, $addonSubscription));
        }
        return response()->json('OK');
    }
    /**
     * Cancel the team's add-on subscription.
     *
     * @param  Request  $request
     * @param  int  $addonId
     * @return Response
     */
    public function cancel(Request $request, $addonId)
    {
        $user = $request->user();
        // Get the add-on subscription
        $addonSubscription = $user->addonSubscriptions()->findOrFail($addonId);
        // Find the add-on plan
        $addonPlan = Spark::findAddonPlanById($addonSubscription->subscription->provider_plan);
        // Cancel addon subscription
        Spark::call(CancelAddonSubscription::class, [$addonSubscription, $addonPlan]);
        // Cancel the add-on
        if(!empty($addonPlan->onCancel)) {
            dispatch(new $addonPlan->onCancel($user, $addonSubscription));
        }
        return response()->json('OK');
    }
    /**
     * Return all of the add-on subscriptions for the team.
     *
     * @param  Request  $request
     * @return Response
     */
    public function subscriptions(Request $request)
    {
        return response()->json(Spark::addonSubscriptionsToBeSettled($request->user()));
    }
}
