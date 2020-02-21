<?php

namespace CentralityLabs\SparkAddons;

use CentralityLabs\SparkAddons\Traits\CashierMultiPlanSubscription;

class TeamSubscription extends \Laravel\Spark\TeamSubscription
{
    use CashierMultiPlanSubscription;
}
