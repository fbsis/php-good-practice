<?php

declare(strict_types=1);

namespace Challenge;

use Challenge\Cache\AnalyticsCacheKeyFactory;
use Challenge\Cache\CacheFactory;
use Challenge\Controllers\ActiveVisitorsController;
use Challenge\Controllers\HealthController;
use Challenge\Controllers\SegmentPreviewController;
use Challenge\Database\ConnectionFactory;
use Challenge\Logging\LoggerFactory;
use Challenge\Repositories\SegmentPreviewRepository;
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
        $displayErrorDetails = getenv('APP_ENV') !== 'production';
        $logger = LoggerFactory::createFromEnvironment();
        $cache = CacheFactory::createFromEnvironment();
        $cacheKeyFactory = new AnalyticsCacheKeyFactory();

        $pdo = ConnectionFactory::createFromEnvironment();
        $visitorAnalyticsRepository = new VisitorAnalyticsRepository($pdo);
        $segmentPreviewRepository = new SegmentPreviewRepository($pdo);
        $visitorAnalyticsService = new VisitorAnalyticsService($visitorAnalyticsRepository, $cache, $cacheKeyFactory);
        $segmentPreviewService = new SegmentPreviewService($segmentPreviewRepository, $cache, $cacheKeyFactory);
        $segmentPreviewValidator = new SegmentPreviewValidator();

        $healthController = new HealthController($pdo);
        $activeVisitorsController = new ActiveVisitorsController($visitorAnalyticsService);
        $segmentPreviewController = new SegmentPreviewController($segmentPreviewValidator, $segmentPreviewService);
        $router = new Router($healthController, $activeVisitorsController, $segmentPreviewController);

        $router->register($app);

        $app->addErrorMiddleware($displayErrorDetails, true, true, $logger);

        return $app;
    }
}
