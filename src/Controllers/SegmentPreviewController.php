<?php

declare(strict_types=1);

namespace Challenge\Controllers;

use Challenge\Http\JsonResponder;
use Challenge\Services\SegmentPreviewService;
use Challenge\Validation\SegmentPreviewValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SegmentPreviewController
{
    public function __construct(
        private readonly SegmentPreviewValidator $validator,
        private readonly SegmentPreviewService $segmentPreviewService,
    ) {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $validation = $this->validator->validate($request->getParsedBody());

        if (!$validation->valid) {
            return JsonResponder::json($response, [
                'error' => 'validation_failed',
                'fields' => $validation->fields,
            ], 422);
        }

        $accountId = (int) $args['accountId'];
        $preview = $this->segmentPreviewService->preview($accountId, $validation->value);

        return JsonResponder::json($response, $preview);
    }
}
