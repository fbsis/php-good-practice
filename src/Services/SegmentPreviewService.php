<?php

declare(strict_types=1);

namespace Challenge\Services;

use Challenge\Cache\AnalyticsCacheKeyFactory;
use Challenge\Dto\SegmentPreviewCriteria;
use Challenge\Repositories\SegmentPreviewRepository;
use Psr\Cache\CacheItemPoolInterface;

final class SegmentPreviewService
{
    private const CACHE_TTL_SECONDS = 60;

    public function __construct(
        private readonly SegmentPreviewRepository $segmentPreviewRepository,
        private readonly CacheItemPoolInterface $cache,
        private readonly AnalyticsCacheKeyFactory $cacheKeyFactory,
    ) {
    }

    /**
     * @return array{count: int, visitors: list<array<string, scalar|null>>}
     */
    public function preview(int $accountId, SegmentPreviewCriteria $criteria): array
    {
        $cacheItem = $this->cache->getItem($this->cacheKeyFactory->segmentPreview($accountId, $criteria));

        if ($cacheItem->isHit()) {
            $cachedPreview = $cacheItem->get();

            if (is_array($cachedPreview)) {
                return $cachedPreview;
            }
        }

        $preview = [
            'count' => $this->segmentPreviewRepository->countMatchingVisitors($accountId, $criteria),
            'visitors' => $this->segmentPreviewRepository->matchingVisitors($accountId, $criteria),
        ];

        $cacheItem->set($preview)->expiresAfter(self::CACHE_TTL_SECONDS);
        $this->cache->save($cacheItem);

        return $preview;
    }
}
