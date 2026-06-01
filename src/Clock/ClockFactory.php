<?php

declare(strict_types=1);

namespace Challenge\Clock;

use Psr\Clock\ClockInterface;

final class ClockFactory
{
    public static function createFromEnvironment(): ClockInterface
    {
        return new SystemClock();
    }
}
