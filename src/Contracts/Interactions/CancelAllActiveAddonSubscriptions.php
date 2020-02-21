<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface CancelAllActiveAddonSubscriptions
{
    /**
     * Cancels all active add-on subscriptions.
     *
     * @param mixed $owner
     * @return void
     */
    public function handle($owner, $recordUsage = false);
}
