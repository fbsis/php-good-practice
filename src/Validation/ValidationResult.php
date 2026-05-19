<?php

declare(strict_types=1);

namespace Challenge\Validation;

use Challenge\Dto\SegmentPreviewCriteria;

final readonly class ValidationResult
{
    /**
     * @param array<string, list<string>> $fields
     */
    private function __construct(
        public bool $valid,
        public array $fields,
        public ?SegmentPreviewCriteria $value,
    ) {
    }

    public static function valid(SegmentPreviewCriteria $value): self
    {
        return new self(true, [], $value);
    }

    /**
     * @param array<string, list<string>> $fields
     */
    public static function invalid(array $fields): self
    {
        return new self(false, $fields, null);
    }
}
