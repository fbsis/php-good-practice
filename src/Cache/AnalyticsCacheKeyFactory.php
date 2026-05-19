<?php

declare(strict_types=1);

namespace Challenge\Cache;

use Challenge\Dto\SegmentPreviewCriteria;

use function hash;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class AnalyticsCacheKeyFactory
{
    public function activeVisitors(int $accountId, string $from, string $to): string
    {
        return 'active_visitors_' . $this->hash([
            'account_id' => $accountId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function segmentPreview(int $accountId, SegmentPreviewCriteria $criteria): string
    {
        return 'segment_preview_' . $this->hash([
            'account_id' => $accountId,
            'visited_path' => $criteria->visitedPath,
            'min_page_views' => $criteria->minPageViews,
            'identified_only' => $criteria->identifiedOnly,
            'from' => $criteria->from,
            'to' => $criteria->to,
            'limit' => $criteria->limit,
        ]);
    }

    /**
     * @param array<string, scalar> $parts
     */
    private function hash(array $parts): string
    {
        return hash('sha256', json_encode($parts, JSON_THROW_ON_ERROR));
    }
}
