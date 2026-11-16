<?php

namespace App\Events;

class ReputationActivity
{
    public function __construct(
        public readonly string $userId,
        public readonly string $action,
        public readonly int $points,
        public readonly string $description,
        public readonly string $requestId,
    ) {}
}
