<?php

namespace CentralityLabs\SparkAddons;

use CentralityLabs\SparkAddons\Traits\ManagesAvailableAddons;
use CentralityLabs\SparkAddons\Traits\ManagesAvailableAddonPlans;

class Spark extends \Laravel\Spark\Spark
{
    use ManagesAvailableAddons,
        ManagesAvailableAddonPlans;
}
