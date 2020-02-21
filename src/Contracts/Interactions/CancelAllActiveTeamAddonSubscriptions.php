<?php

namespace CentralityLabs\SparkAddons\Contracts\Interactions;

interface CancelAllActiveTeamAddonSubscriptions
{
    /**
     * Cancels all active team add-on subscriptions.
     *
     * @param mixed $user
     * @param mixed $owner
     * @param bool $recordUsage
     * @return void
     */
    public function handle($user, $owner, $recordUsage = false);
}
