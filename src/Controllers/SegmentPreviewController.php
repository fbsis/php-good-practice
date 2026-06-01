<?php

declare(strict_types=1);

namespace Challenge\Controllers;

use Challenge\Dto\SegmentPreviewCriteria;
use Challenge\Http\JsonResponder;
use Challenge\Services\SegmentPreviewService;
use Challenge\Validation\SegmentPreviewValidator;
use LogicException;
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
        $parsedBody = $request->getParsedBody();
        $validation = $this->validator->validate(is_array($parsedBody) ? $parsedBody : null);

        if (!$validation->valid) {
            return JsonResponder::json($response, [
                'error' => 'validation_failed',
                'fields' => $validation->fields,
            ], 422);
        }

        $accountId = (int) $args['accountId'];
        $criteria = $validation->value;
        self::assertSegmentPreviewCriteria($criteria);

        $preview = $this->segmentPreviewService->preview($accountId, $criteria);

        return JsonResponder::json($response, $preview);
    }

    private static function assertSegmentPreviewCriteria(?SegmentPreviewCriteria $criteria): void
    {
        if ($criteria === null) {
            throw new LogicException('Segment preview criteria is required for valid requests.');
        }
    }
}
