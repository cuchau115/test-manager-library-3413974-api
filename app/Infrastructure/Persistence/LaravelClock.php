<?php

namespace App\Infrastructure\Persistence;

use App\Application\Ports\Clock;
use DateTimeImmutable;

final class LaravelClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
