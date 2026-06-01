<?php

declare(strict_types=1);

namespace Challenge\Controllers;

use Challenge\Http\JsonResponder;
use Challenge\Services\VisitorAnalyticsService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ActiveVisitorsController
{
    public function __construct(private readonly VisitorAnalyticsService $visitorAnalyticsService)
    {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $accountId = (int) $args['accountId'];
        $query = $request->getQueryParams();

        $from = is_string($query['from'] ?? null) ? $query['from'] : '';
        $to = is_string($query['to'] ?? null) ? $query['to'] : '';

        $visitors = $this->visitorAnalyticsService->activeVisitors($accountId, $from, $to);

        return JsonResponder::json($response, [
            'data' => $visitors,
        ]);
    }
}
