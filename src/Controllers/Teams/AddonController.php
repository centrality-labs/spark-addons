<?php

namespace CentralityLabs\SparkAddons\Controllers\Teams;

use Spark;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use CentralityLabs\SparkAddons\Contracts\Interactions\SubscribeTeamAddon;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelTeamAddonSubscription;
use CentralityLabs\SparkAddons\Contracts\Interactions\ResumeTeamAddonSubscription;

class AddonController extends Controller
{
    /**
     * Create the add-on subscription for the team.
     *
     * @param Request $request
     * @param int|string|null $id
     * @param string $addon
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request, $id = null, $addon)
    {
        // Get the team
        if(!is_null($id)) {
            $team = Spark::teamModel()::where(config('spark-addons.teamsIndexKey', 'id'), $id)->firstOrFail();
            abort_unless($request->user()->onTeam($team), 404);
        } else {
            $team = $request->user()->currentTeam;
        }
        // Validate request
        Spark::call(SubscribeTeamAddon::class.'@validator', [
            $team, $request->all()
        ])->validate();
        // Get the add-on plan
        $addonPlan = Spark::findAddonPlanById($request->plan);
        // Create the subscription
        $addonSubscription = Spark::call(SubscribeTeamAddon::class, [
            $team, $addonPlan, $request->all()
        ]);
        // Launch the add-on
        if(!empty($addonPlan->onSubscribe)) {
            dispatch(new $addonPlan->onSubscribe($request->user(), $team, $addonSubscription, $request->all()));
        }
        // Switch to the team so they can jump right in
        $request->user()->switchToTeam($team);
        return response()->json('OK');
    }
    /**
     * Update the subscription for the team.
     *
     * @param Request $request
     * @param int|string|null $id
     * @param int $addonId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id = null, $addonId)
    {
        // Get the team
        if(!is_null($id)) {
            $team = Spark::teamModel()::where(config('spark-addons.teamsIndexKey', 'id'), $id)->firstOrFail();
            abort_unless($request->user()->onTeam($team), 404);
        } else {
            $team = $request->user()->currentTeam;
        }
        // Get the add-on subscription
        $addonSubscription = $team->addonSubscriptions()->findOrFail($addonId);
        // Find the add-on plan
        $addonPlan = Spark::findAddonPlanById($addonSubscription->subscription->provider_plan);
        // Resume
        Spark::call(ResumeTeamAddonSubscription::class, [$addonSubscription, $addonPlan]);
        // Resume the add-on
        if(!empty($addonPlan->onResume)) {
            dispatch(new $addonPlan->onResume($request->user(), $team, $addonSubscription));
        }
        return response()->json('OK');
    }
    /**
     * Cancel the team's add-on subscription.
     *
     * @param Request $request
     * @param int|string|null $id
     * @param $addonId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $id = null, $addonId)
    {
        // Get the team
        if(!is_null($id)) {
            $team = Spark::teamModel()::where(config('spark-addons.teamsIndexKey', 'id'), $id)->firstOrFail();
            abort_unless($request->user()->onTeam($team), 404);
        } else {
            $team = $request->user()->currentTeam;
        }
        // Get the add-on subscription
        $addonSubscription = $team->addonSubscriptions()->findOrFail($addonId);
        // Find the add-on plan
        $addonPlan = Spark::findAddonPlanById($addonSubscription->subscription->provider_plan);
        // Cancel addon subscription
        Spark::call(CancelTeamAddonSubscription::class, [$addonSubscription, $addonPlan]);
        // Cancel the add-on
        if(!empty($addonPlan->onCancel)) {
            dispatch(new $addonPlan->onCancel($request->user(), $team, $addonSubscription));
        }
        return response()->json('OK');
    }
    /**
     * Return all of the add-on subscriptions for the team.
     *
     * @param Request $request
     * @param int|string|null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscriptions(Request $request, $id = null)
    {
        // Get the team
        if(!is_null($id)) {
            $team = Spark::teamModel()::where(config('spark-addons.teamsIndexKey', 'id'), $id)->firstOrFail();
            abort_unless($request->user()->onTeam($team), 404);
        } else {
            $team = $request->user()->currentTeam;
        }
        return response()->json(Spark::addonSubscriptionsToBeSettled($team));
    }
}
