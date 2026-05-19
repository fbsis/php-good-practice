<?php

declare(strict_types=1);

namespace Challenge\Validation;

use Challenge\Dto\SegmentPreviewCriteria;
use DateTimeImmutable;

final class SegmentPreviewValidator
{
    /**
     * @param array<string, array<string, string|int|bool>|int>|null $payload
     */
    public function validate(?array $payload): ValidationResult
    {
        $fields = [];

        if ($payload === null) {
            return ValidationResult::invalid([
                'body' => ['Must be a JSON object.'],
            ]);
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
            return ValidationResult::invalid($fields);
        }

        return ValidationResult::valid(new SegmentPreviewCriteria(
            visitedPath: $visitedPath,
            minPageViews: $minPageViews,
            identifiedOnly: $identifiedOnly,
            from: $from,
            to: $to,
            limit: $limit,
        ));
    }

    private function isDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
    }
}
