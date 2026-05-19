<?php

declare(strict_types=1);

namespace Challenge;

use Challenge\Controllers\ActiveVisitorsController;
use Challenge\Controllers\HealthController;
use Challenge\Controllers\SegmentPreviewController;
use Challenge\Database\ConnectionFactory;
use Challenge\Repositories\VisitorAnalyticsRepository;
use Challenge\Services\SegmentPreviewService;
use Challenge\Services\VisitorAnalyticsService;
use Challenge\Validation\SegmentPreviewValidator;
use Slim\App;
use Slim\Factory\AppFactory as SlimAppFactory;

final class AppFactory
{
    public static function create(): App
    {
        $app = SlimAppFactory::create();
        $app->addBodyParsingMiddleware();

        $pdo = ConnectionFactory::createFromEnvironment();
        $visitorAnalyticsRepository = new VisitorAnalyticsRepository($pdo);
        $visitorAnalyticsService = new VisitorAnalyticsService($visitorAnalyticsRepository);
        $segmentPreviewService = new SegmentPreviewService($visitorAnalyticsRepository);
        $segmentPreviewValidator = new SegmentPreviewValidator();

        $healthController = new HealthController($pdo);
        $activeVisitorsController = new ActiveVisitorsController($visitorAnalyticsService);
        $segmentPreviewController = new SegmentPreviewController($segmentPreviewValidator, $segmentPreviewService);

        $app->get('/health', $healthController);
        $app->get('/api/accounts/{accountId}/visitors/active', $activeVisitorsController);
        $app->post('/api/accounts/{accountId}/segments/preview', $segmentPreviewController);

        $app->addErrorMiddleware(true, true, true);

        return $app;
    }
}
