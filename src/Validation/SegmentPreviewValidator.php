<?php

declare(strict_types=1);

namespace Challenge\Validation;

use DateTimeImmutable;

final class SegmentPreviewValidator
{
    /**
     * @param mixed $payload
     * @return array{
     *     valid: bool,
     *     fields: array<string, list<string>>,
     *     value: array{
     *         visited_path: string,
     *         min_page_views: int,
     *         identified_only: bool,
     *         from: string,
     *         to: string,
     *         limit: int
     *     }|null
     * }
     */
    public function validate(mixed $payload): array
    {
        $fields = [];

        if (!is_array($payload)) {
            return [
                'valid' => false,
                'fields' => [
                    'body' => ['Must be a JSON object.'],
                ],
                'value' => null,
            ];
        }

        $rules = $payload['rules'] ?? null;
        if (!is_array($rules)) {
            $rules = [];
            $fields['rules'] = ['Must be an object.'];
        }

        $visitedPath = $rules['visited_path'] ?? null;
        if (!is_string($visitedPath) || trim($visitedPath) === '') {
            $fields['rules.visited_path'] = ['Must be a non-empty string.'];
            $visitedPath = '';
        }

        $minPageViews = $rules['min_page_views'] ?? null;
        if (!is_int($minPageViews) || $minPageViews < 1) {
            $fields['rules.min_page_views'] = ['Must be an integer greater than or equal to 1.'];
            $minPageViews = 0;
        }

        $identifiedOnly = $rules['identified_only'] ?? null;
        if (!is_bool($identifiedOnly)) {
            $fields['rules.identified_only'] = ['Must be a boolean.'];
            $identifiedOnly = false;
        }

        $from = $rules['from'] ?? null;
        if (!is_string($from) || !$this->isDate($from)) {
            $fields['rules.from'] = ['Must be a valid YYYY-MM-DD date.'];
            $from = '';
        }

        $to = $rules['to'] ?? null;
        if (!is_string($to) || !$this->isDate($to)) {
            $fields['rules.to'] = ['Must be a valid YYYY-MM-DD date.'];
            $to = '';
        }

        if (
            is_string($from)
            && is_string($to)
            && $this->isDate($from)
            && $this->isDate($to)
            && $from > $to
        ) {
            $fields['rules.from'] = ['Must be earlier than or equal to rules.to.'];
        }

        $limit = $payload['limit'] ?? 25;
        if (!is_int($limit) || $limit < 1 || $limit > 100) {
            $fields['limit'] = ['Must be an integer between 1 and 100.'];
            $limit = 25;
        }

        if ($fields !== []) {
            return [
                'valid' => false,
                'fields' => $fields,
                'value' => null,
            ];
        }

        return [
            'valid' => true,
            'fields' => [],
            'value' => [
                'visited_path' => $visitedPath,
                'min_page_views' => $minPageViews,
                'identified_only' => $identifiedOnly,
                'from' => $from,
                'to' => $to,
                'limit' => $limit,
            ],
        ];
    }

    private function isDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
    }
}
