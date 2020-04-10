<?php
namespace CentralityLabs\SparkAddons;
use Illuminate\Support\ServiceProvider;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelAllActiveAddonSubscriptions;
use CentralityLabs\SparkAddons\Contracts\Interactions\CancelAllActiveTeamAddonSubscriptions;
class SparkAddonsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishesAll();
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'spark-addons');
    }
    protected function publishesAll()
    {
        $this->publishes([
            __DIR__.'/../config/spark-addons.php' => config_path('spark-addons.php'),
        ], 'spark-addons-config');
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'spark-addons-migrations');
        $this->publishes([
            __DIR__.'/../resources/assets/js' => resource_path('assets/js/spark-addon-components'),
        ], 'spark-addons-assets');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/spark-addons'),
        ], 'spark-addons-views');
    }
    /**
     * Register the Spark Addon services.
     *
     * @return void
     */
    protected function registerServices()
    {
        $services = [
            'Contracts\Interactions\CalculateMeteredUsage' => 'Interactions\CalculateMeteredUsage',
            'Contracts\Interactions\RecordUsage' => 'Interactions\RecordUsageUsingStripe',
            'Contracts\Interactions\SubscribeAddon' => 'Interactions\SubscribeAddon',
            'Contracts\Interactions\SubscribeTeamAddon' => 'Interactions\SubscribeTeamAddon',
            'Contracts\Interactions\ResumeAddonSubscription' => 'Interactions\ResumeAddonSubscription',
            'Contracts\Interactions\ResumeTeamAddonSubscription' => 'Interactions\ResumeTeamAddonSubscription',
            'Contracts\Interactions\CancelAddonSubscription' => 'Interactions\CancelAddonSubscription',
            'Contracts\Interactions\CancelTeamAddonSubscription' => 'Interactions\CancelTeamAddonSubscription',
            'Contracts\Interactions\CancelAllActiveAddonSubscriptions' => 'Interactions\CancelAllActiveAddonSubscriptions',
            'Contracts\Interactions\CancelAllActiveTeamAddonSubscriptions' => 'Interactions\CancelAllActiveTeamAddonSubscriptions',
        ];
        foreach ($services as $key => $value) {
            $this->app->singleton('CentralityLabs\SparkAddons\\'.$key, 'CentralityLabs\SparkAddons\\'.$value);
        }
    }

    **
     * Register package macros.
     */
    protected function registerMacro(): void
    {
        Str::macro('SparkIntervalToStripeInterval', function ($string) {
            switch($string)
            {
                case 'daily': 
                    return 'day';
                case 'weekly': 
                    return 'week';
                case 'monthly': 
                    return 'month';
                case 'yearly': 
                    return 'year';
                default:
                    return $string;
            }
        });
    }
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/spark-addons.php', 'spark-addons');
        $this->registerServices();
        $this->registerMacro();

        // User has cancelled their subscription. Cancel all of their add-on subscriptions.
        // When Stripe fires the `invoice.created` webhook at billing cycle end, their
        // usage will be calculated and recorded with Stripe for their final charge.
        \Event::listen('Laravel\Spark\Events\Subscription\SubscriptionCancelled', function ($event) {
            Spark::call(CancelAllActiveAddonSubscriptions::class, [$event->user]);
        });
        // Team has cancelled their subscription. Cancel all of their add-on subscriptions.
        // When Stripe fires the `invoice.created` webhook at billing cycle end, their
        // usage will be calculated and recorded with Stripe for their final charge.
        \Event::listen('Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled', function ($event) {
            Spark::call(CancelAllActiveTeamAddonSubscriptions::class, [auth()->user(), $event->team]);
        });
        // When a team is deleted, it's removed from the database. We cancel the subscription for a
        // deleted team via cancel_at_period_end to get a final invoice where we can record their
        // remaining usage. The usage needs to be recorded now because when the invoice.created
        // webhook is fired off for the final invoice, the team db record is no longer there.
        \Event::listen('Laravel\Spark\Events\Teams\DeletingTeam', function ($event) {
            Spark::call(CancelAllActiveTeamAddonSubscriptions::class, [auth()->user(), $event->team, true]);
        });
    }
}
