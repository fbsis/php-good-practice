<?php

declare(strict_types=1);

namespace Challenge\Cache;

use Psr\Cache\CacheItemPoolInterface;

final class CacheFactory
{
    public static function createFromEnvironment(): CacheItemPoolInterface
    {
        return new MemoryCacheItemPool();
    }
}
