<?php

namespace App\Listeners;

use App\Events\ReputationActivity;
use App\Services\ReputationService;

class AwardReputationPoints
{
    public function __construct(private readonly ReputationService $reputation) {}

    public function handle(ReputationActivity $event): void
    {
        $this->reputation->award(
            $event->userId,
            $event->action,
            $event->points,
            $event->description,
            $event->requestId,
        );
    }
}
