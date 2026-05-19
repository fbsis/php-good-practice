<?php

declare(strict_types=1);

namespace Challenge\Services;

use Challenge\Repositories\VisitorAnalyticsRepository;

final class VisitorAnalyticsService
{
    public function __construct(private readonly VisitorAnalyticsRepository $visitorAnalyticsRepository)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function activeVisitors(int $accountId, string $from, string $to): array
    {
        return $this->visitorAnalyticsRepository->activeVisitors($accountId, $from, $to);
    }
}
