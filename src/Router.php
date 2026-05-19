<?php

declare(strict_types=1);

namespace Challenge;

use Challenge\Controllers\ActiveVisitorsController;
use Challenge\Controllers\HealthController;
use Challenge\Controllers\SegmentPreviewController;
use Slim\App;

final class Router
{
    public function __construct(
        private readonly HealthController $healthController,
        private readonly ActiveVisitorsController $activeVisitorsController,
        private readonly SegmentPreviewController $segmentPreviewController,
    ) {
    }

    public function register(App $app): void
    {
        $app->get('/health', $this->healthController);
        $app->get('/api/accounts/{accountId}/visitors/active', $this->activeVisitorsController);
        $app->post('/api/accounts/{accountId}/segments/preview', $this->segmentPreviewController);
    }
}
