<?php

namespace CentralityLabs\SparkAddons;

use CentralityLabs\SparkAddons\Traits\CashierMultiPlanSubscription;

class Subscription extends \Laravel\Spark\Subscription
{
    use CashierMultiPlanSubscription;
}
