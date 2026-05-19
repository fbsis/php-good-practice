<?php

declare(strict_types=1);

namespace Challenge\Tests\Unit\Cache;

use Challenge\Cache\InvalidCacheKeyException;
use Challenge\Cache\MemoryCacheItemPool;
use PHPUnit\Framework\TestCase;

final class MemoryCacheItemPoolTest extends TestCase
{
    public function testSavesAndReadsCacheItem(): void
    {
        $pool = new MemoryCacheItemPool();

        $item = $pool->getItem('analytics_key');
        self::assertFalse($item->isHit());

        $item->set(['count' => 1]);
        self::assertTrue($pool->save($item));

        $cachedItem = $pool->getItem('analytics_key');

        self::assertTrue($cachedItem->isHit());
        self::assertSame(['count' => 1], $cachedItem->get());
    }

    public function testExpiresCacheItem(): void
    {
        $pool = new MemoryCacheItemPool();

        $item = $pool->getItem('short_lived_key');
        $item->set('stale')->expiresAfter(-1);
        self::assertTrue($pool->save($item));

        $cachedItem = $pool->getItem('short_lived_key');

        self::assertFalse($cachedItem->isHit());
        self::assertNull($cachedItem->get());
    }

    public function testCommitsDeferredItems(): void
    {
        $pool = new MemoryCacheItemPool();

        $item = $pool->getItem('deferred_key');
        $item->set('ready');

        self::assertTrue($pool->saveDeferred($item));
        self::assertFalse($pool->hasItem('deferred_key'));

        self::assertTrue($pool->commit());
        self::assertTrue($pool->hasItem('deferred_key'));
    }

    public function testRejectsInvalidCacheKey(): void
    {
        $pool = new MemoryCacheItemPool();

        $this->expectException(InvalidCacheKeyException::class);

        $pool->getItem('invalid/key');
    }
}
