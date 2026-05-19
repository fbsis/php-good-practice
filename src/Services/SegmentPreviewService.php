<?php

declare(strict_types=1);

namespace Challenge\Services;

use Challenge\Dto\SegmentPreviewCriteria;
use Challenge\Repositories\SegmentPreviewRepository;

final class SegmentPreviewService
{
    public function __construct(private readonly SegmentPreviewRepository $segmentPreviewRepository)
    {
    }

    /**
     * @return array{count: int, visitors: list<array<string, scalar|null>>}
     */
    public function preview(int $accountId, SegmentPreviewCriteria $criteria): array
    {
        return [
            'count' => $this->segmentPreviewRepository->countMatchingVisitors($accountId, $criteria),
            'visitors' => $this->segmentPreviewRepository->matchingVisitors($accountId, $criteria),
        ];
    }
}
