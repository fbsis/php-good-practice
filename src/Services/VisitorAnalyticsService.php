<?php

declare(strict_types=1);

namespace Challenge\Services;

use Challenge\Cache\AnalyticsCacheKeyFactory;
use Challenge\Repositories\VisitorAnalyticsRepository;
use Psr\Cache\CacheItemPoolInterface;

final class VisitorAnalyticsService
{
    private const CACHE_TTL_SECONDS = 60;

    public function __construct(
        private readonly VisitorAnalyticsRepository $visitorAnalyticsRepository,
        private readonly CacheItemPoolInterface $cache,
        private readonly AnalyticsCacheKeyFactory $cacheKeyFactory,
    ) {
    }

    /**
     * @return list<array<string, scalar|null>>
     */
    public function activeVisitors(int $accountId, string $from, string $to): array
    {
        $cacheItem = $this->cache->getItem($this->cacheKeyFactory->activeVisitors($accountId, $from, $to));

        if ($cacheItem->isHit()) {
            $cachedVisitors = $cacheItem->get();

            if (is_array($cachedVisitors)) {
                return $cachedVisitors;
            }
        }

        $visitors = $this->visitorAnalyticsRepository->activeVisitors($accountId, $from, $to);

        $cacheItem->set($visitors)->expiresAfter(self::CACHE_TTL_SECONDS);
        $this->cache->save($cacheItem);

        return $visitors;
    }
}
