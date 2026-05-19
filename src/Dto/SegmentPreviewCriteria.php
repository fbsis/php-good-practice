<?php

declare(strict_types=1);

namespace Challenge\Dto;

final readonly class SegmentPreviewCriteria
{
    public function __construct(
        public string $visitedPath,
        public int $minPageViews,
        public bool $identifiedOnly,
        public string $from,
        public string $to,
        public int $limit,
    ) {
    }
}
