<?php

declare(strict_types=1);

namespace Challenge\Tests\Unit\Cache;

use Challenge\Cache\InvalidCacheKeyException;
use Challenge\Cache\MemoryCacheItemPool;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;
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
        $clock = new MutableClock(new DateTimeImmutable('2026-05-19 12:00:00 UTC'));
        $pool = new MemoryCacheItemPool($clock);

        $item = $pool->getItem('short_lived_key');
        $item->set('stale')->expiresAfter(10);
        self::assertTrue($pool->save($item));
        self::assertTrue($pool->hasItem('short_lived_key'));

        $clock->advanceSeconds(11);

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

final class MutableClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function advanceSeconds(int $seconds): void
    {
        $this->now = $this->now->modify('+' . $seconds . ' seconds');
    }
}
