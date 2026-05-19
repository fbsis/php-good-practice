<?php

declare(strict_types=1);

namespace Challenge\Services;

use Challenge\Repositories\SegmentPreviewRepository;

final class SegmentPreviewService
{
    public function __construct(private readonly SegmentPreviewRepository $segmentPreviewRepository)
    {
    }

    /**
     * @param array{
     *     visited_path: string,
     *     min_page_views: int,
     *     identified_only: bool,
     *     from: string,
     *     to: string,
     *     limit: int
     * } $rules
     * @return array{count: int, visitors: list<array<string, mixed>>}
     */
    public function preview(int $accountId, array $rules): array
    {
        return [
            'count' => $this->segmentPreviewRepository->countMatchingVisitors($accountId, $rules),
            'visitors' => $this->segmentPreviewRepository->matchingVisitors($accountId, $rules),
        ];
    }
}
