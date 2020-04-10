# Spark Addons

Grow your revenue by offering multiple add-on subscriptions in your Laravel Spark app.

- Define "add-on" subscriptions
- Supported metered (pay as you go) and licensed (monthly fee) usage types
- Support multiple plans for each add-on
- Allow users to subscribe and cancel their subscription to an add-on plan
- Implemented in a similar style to existing Spark functionality

## Forked

This respository was forked from `https://github.com/benmag/laravel-spark-addons#readme` which unforuntaely isn't available any longer.

## Installation

1. Include the package

```composer
  composer require centality-labs/spark-addons
```

2. Publish and run the migrations

```php
  php artisan vendor:publish --tag=spark-addons-migrations && php artisan migrate
```

3. Update change your Spark class in config/app.php

```php
  [
      'aliases' => [
          'Spark' => \CentralityLabs\SparkAddons\Spark::class,
      ]
  ]
```

4. Switch the use `Laravel\Spark\Spark;` class in `SparkServiceProvider` to use `CentralityLabs\SparkAddons\Spark;`

5. Add the BillableAddonSubscriptions trait to your Team model

```php
  namespace App;

  use Laravel\Spark\Team as SparkTeam;
  use CentrailityLabs\SparkAddons\Traits\BillableAddonSubscriptions;

  class Team extends SparkTeam
  {
    use BillableAddonSubscriptions;
    //...
  }
```

6. Ensure the Vue components for spark-addons get built by adding the following to your `resources/assets/js/components/bootstrap.js` file.

```js
require("./../../../vendor/centrality-labs/spark-addons/resources/assets/js/bootstrap");
```

7. Add your spark-addons config to your global Spark object in your layout file (resources/views/vendor/spark/layouts/app.blade.php)

```javascript
<!-- Global Spark Object -->
<script>
    window.Spark = <?php echo json_encode(array_merge(
        Spark::scriptVariables(), [
            'spark-addons' => config('spark-addons')
        ]
    )); ?>
</script>
```

8. Show current add-on subscriptions in the subscriptions page by adding the following to the `views/vendor/spark/settings/subscription.blade.php` view

```php
   <addon-subscriptions
       :user="user"
       :team="team"
       :billable-type="billableType">
   </addon-subscriptions>
```

## Usage

You can now define available add-on plans in a similar style to how you define your Spark plans inside your SparkServiceProvider.

```php
// SocketCluster
Spark::addon(new \CentalityLabs\SparkAddons\Addon([
  'id' => 'socketcluster',
  'name' => 'SocketCluster',
  'description' => "A scalable framework for real-time apps and services.",
]));

// SocketCluster - Plan: Hobby
Spark::addonPlan('socketcluster', 'Hobby', 'socketcluster:hobby')
  ->price(19)
  ->trialDays(15)
  ->features([
    "Powered by 512mb RAM",
    "No complicated setup",
    "Email support"
  ])
  ->attributes([
    'description' => "Single 512mb SocketCluster instance, perfect for development",
]);

// Servers
Spark::addon(new \CentalityLabs\SparkAddons\Addon([
  'id' => 'servers',
  'name' => 'Servers',
  'description' => "High performance cloud servers provisioned for you.",
]));

// Servers: Standard 1X
Spark::addonPlan('servers', 'Standard 1X', 'servers:standard-1x')
  ->usageType('metered')
  ->price(0.10)
  ->features([
    '512MB RAM',
    '1 Core Processor',
    '20GB SSD Disk',
    '1TB Transfer'
  ])
  ->attributes([
    'description' => "512MB RAM. Perfect server to power hobby and small apps.",
  ]);
```

## Configuration

1. Publish config

```php
  php artisan vendor:publish --tag=spark-addons-config
```

2. Publish assets

```php
  php artisan vendor:publish --tag=spark-addons-assets
```

3. Publish views

```php
php artisan vendor:publish --tag=spark-addons-views
```

## Metered Billing

Metered billing or usage based billing allows you to charge your customer an amount based on what they have actually consumed by taking advantage of [Stripe's metered billing](https://stripe.com/docs/billing/subscriptions/metered-billing).

For example, at Codemason, our users can use Codemason Servers to run their apps. These servers are billed by the hour at the corresponding rate and can be cancelled at any time. At the end of the month, the customer is billed for the amount of hours they have accumulated.

By default metered add-on plans are measuring usage by the hour. However, it's easy to swap this implementation out for your own, just as you would for any other Spark interaction with the `*Spark::swap*` technique.

The behind the scenes of how this works is fairly simple: at the end of the billing cylce, Stripe fires a `invoice.created` webhook approximately an hour before the invoice is finalised and a charge is attempted. When your app receives that webhook, it calculates the number of hours the add-on has been active for and updates Stripe with the usage.

To make use of this functionality, you will make some additional configurations. Don't worry, it's still very easy and very straight forward.

### _Override the default Stripe Webhook (required)_

The main thing we will need to do is make sure that the `SparkAddonsServiceProvider` is being manually registered after the `SparkServiceProvider`. This is to ensure that we're using our new controller that listens for the `invoice.created` webhook to perform usage calculations at the end of the month.

Disable auto-discovery so we can override the existing `spark/webhoo`k that Laravel Spark provides

Do this by adding the following to your `composer.json` file:

```json
"extra": {
  "laravel": {
    "dont-discover": [
      "laravel/dusk",
      "centrality-labs/spark-addons"
    ]
  }
},
```

Manually register the `SparkAddonsServiceProvider` after the `SparkServiceProvider` in the providers array in `config/app.php`

```php
'providers' => [
  Laravel\Spark\Providers\SparkServiceProvider::class,
  App\Providers\SparkServiceProvider::class,
  CentralityLabs\SparkAddons\SparkAddonsServiceProvider::class,
  // ...
]
```

## Interactions

These are the interactions, you are able to override them with your own custom implementations where required using `*Spark::swap*`

| Interaction                 | Description                                                                                                                 |
| --------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| CalculateMeteredUsage       | Calculate the hour based usage for the add-on subscription.                                                                 |
| RecordUsageUsingStripe      | Records the usage on Stripe                                                                                                 |
| SubscribeAddon              | Adds a new add-on subscription to a owner                                                                                   |
| SubscribeTeamAddon          | Adds a new add-on subscription to a owner                                                                                   |
| CancelAddonSubscription     | Cancels an add-on subscription. If the add-on subscription is license based, it will reduce the subscription quantity       |
| CancelTeamAddonSubscription | Cancels an add-on subscription. If the add-on subscription is license based, it will reduce the subscription quantity       |
| ResumeAddonSubscription     | Resume a cancelled add-on subscription (only available if the add-on subscription is license based and in the grace period) |
| ResumeTeamAddonSubscription | Resume a cancelled add-on subscription (only available if the add-on subscription is license based and in the grace period) |
