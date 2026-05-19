<?php

declare(strict_types=1);

namespace Challenge\Logging;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Psr\Log\AbstractLogger;
use Stringable;
use Throwable;

use function get_debug_type;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_null;
use function is_resource;
use function is_scalar;
use function is_string;
use function json_encode;
use function sprintf;
use function strtr;

use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_THROW_ON_ERROR;
use const PHP_EOL;

final class StderrLogger extends AbstractLogger
{
    /** @var resource */
    private $stream;

    /**
     * @param resource|null $stream
     */
    public function __construct($stream = null)
    {
        if ($stream === null) {
            $stream = fopen('php://stderr', 'ab');
        }

        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Logger stream must be a valid resource.');
        }

        $this->stream = $stream;
    }

    /**
     * @param string|Stringable $level
     * @param array<string, scalar|Stringable|Throwable|array<array-key, scalar|Stringable|Throwable|object|resource|null>|object|resource|null> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $record = [
            'timestamp' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ATOM),
            'level' => (string) $level,
            'message' => $this->interpolate((string) $message, $context),
            'context' => $this->normalizeContext($context),
        ];

        fwrite(
            $this->stream,
            json_encode($record, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL
        );
    }

    /**
     * @param array<string, scalar|Stringable|Throwable|array<array-key, scalar|Stringable|Throwable|object|resource|null>|object|resource|null> $context
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];

        foreach ($context as $key => $value) {
            if (is_null($value) || is_scalar($value) || $value instanceof Stringable) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * @param array<string, scalar|Stringable|Throwable|array<array-key, scalar|Stringable|Throwable|object|resource|null>|object|resource|null> $context
     * @return array<string, scalar|array<array-key, scalar|array<string, scalar>|null>|null>
     */
    private function normalizeContext(array $context): array
    {
        $normalized = [];

        foreach ($context as $key => $value) {
            $normalized[$key] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    /**
     * @param scalar|Stringable|Throwable|array<array-key, scalar|Stringable|Throwable|object|resource|null>|object|resource|null $value
     * @return scalar|array<array-key, scalar|array<string, scalar>|null>|null
     */
    private function normalizeValue($value): string|int|float|bool|array|null
    {
        if ($value instanceof Throwable) {
            return [
                'class' => $value::class,
                'message' => $value->getMessage(),
                'file' => $value->getFile(),
                'line' => $value->getLine(),
            ];
        }

        if (is_null($value) || is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
            return $value;
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $nestedValue) {
                $normalized[$key] = $this->normalizeValue($nestedValue);
            }

            return $normalized;
        }

        if (is_resource($value)) {
            return sprintf('[resource:%s]', get_debug_type($value));
        }

        return sprintf('[%s]', get_debug_type($value));
    }
}
