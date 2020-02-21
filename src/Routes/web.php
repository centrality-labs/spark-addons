<?php
/**
 * Helpers.
 */
$route = function ($accessor, $default = '') {
    return $this->app->config->get('spark-addons.routes.'.$accessor, $default);
};
/**
 * Spark Addon routes.
 */
Route::group(['middleware' => 'web'], function() use ($route) {
    Route::get($route('addons').'/{addon}', function($addon) {
        return view('spark-addons::addon')
            ->with('addon', \Spark::findAddonById($addon));
    });
});
Route::group([
    'namespace' => 'CentralityLabs\SparkAddons\Controllers',
    'prefix' => $route('api')
], function () use ($route) {
    Route::get($route('addons'), 'AddonController@index');
    if(\Spark::canBillCustomers()) {
        Route::get($route('addons') . '/subscriptions', 'AddonController@subscriptions')->middleware('auth:api');
    }
    Route::get($route('addons') . '/{addon}', 'AddonController@show');
    Route::get($route('addons') . '/{addon}/' . $route('plans'), 'AddonController@plans');
    Route::post($route('addons') . '/{addon}/' . $route('subscribe'), 'AddonController@subscribe')->middleware('auth:api');
    Route::put($route('addons') . '/{addon}/' . $route('subscribe'), 'AddonController@update')->middleware('auth:api');
    Route::delete($route('addons') . '/{addonId}/' . $route('cancel'), 'AddonController@cancel')->middleware('auth:api');
    // Teams...
    if(\Spark::canBillTeams()) {
        Route::get(
            '/settings/'.Spark::teamsPrefix().'/{team}/'.$route('addons').'/'.$route('subscriptions'),
            'Teams\AddonController@subscriptions'
        )->middleware('auth:api');
        Route::post(
            '/settings/'.Spark::teamsPrefix().'/{team}/'.$route('addons').'/{addon}/'.$route('subscribe'),
            'Teams\AddonController@subscribe'
        )->middleware('auth:api');
        Route::put(
            '/settings/'.Spark::teamsPrefix().'/{team}/'.$route('addons').'/{addon}/'.$route('subscribe'),
            'Teams\AddonController@update'
        )->middleware('auth:api');
        Route::delete(
            '/settings/'.Spark::teamsPrefix().'/{team}/'.$route('addons').'/{addonId}/'.$route('cancel'),
            'Teams\AddonController@cancel'
        )->middleware('auth:api');
    }
    // Teams identified by path...
    if(\Spark::canBillTeams() && config('spark-addons.teamsIdentifiedByPath', false)) {
        Route::get('{slug}/'.$route('addons').'/'.$route('subscriptions'), 'Teams\AddonController@subscriptions')->middleware('auth:api');
        Route::post('{slug}/'.$route('addons').'/{addon}/'.$route('subscribe'), 'Teams\AddonController@subscribe')->middleware('auth:api');
        Route::put('{slug}/'.$route('addons').'/{addon}/'.$route('subscribe'), 'Teams\AddonController@update')->middleware('auth:api');
        Route::delete('{slug}/'.$route('addons').'/{addonId}/'.$route('cancel'), 'Teams\AddonController@cancel')->middleware('auth:api');
    }
});
// Webhooks...
Route::post('webhook/stripe', 'CentralityLabs\SparkAddons\Controllers\StripeWebhookController@handleWebhook');
