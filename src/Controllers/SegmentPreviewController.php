<?php

declare(strict_types=1);

namespace Challenge\Controllers;

use Challenge\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SegmentPreviewController
{
    public function __invoke(Request $request, Response $response): Response
    {
        return JsonResponder::json($response, [
            'error' => 'not_implemented',
            'message' => 'Segment preview is intentionally incomplete for this assessment.',
        ], 501);
    }
}
