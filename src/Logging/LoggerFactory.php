<?php

declare(strict_types=1);

namespace Challenge\Logging;

use Psr\Log\LoggerInterface;

final class LoggerFactory
{
    public static function createFromEnvironment(): LoggerInterface
    {
        return new StderrLogger();
    }
}
