<?php

declare(strict_types=1);

namespace Challenge\Tests\Unit\Logging;

use Challenge\Logging\StderrLogger;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class StderrLoggerTest extends TestCase
{
    public function testWritesJsonLogRecordToStream(): void
    {
        $stream = fopen('php://memory', 'w+');
        self::assertIsResource($stream);

        $logger = new StderrLogger($stream);
        $exception = new RuntimeException('Database unavailable.');

        $logger->error('Request {request_id} failed.', [
            'request_id' => 'req_123',
            'exception' => $exception,
        ]);

        rewind($stream);
        $line = fgets($stream);
        self::assertIsString($line);

        $record = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($record);

        self::assertSame('error', $record['level']);
        self::assertSame('Request req_123 failed.', $record['message']);
        self::assertSame('req_123', $record['context']['request_id']);
        self::assertSame(RuntimeException::class, $record['context']['exception']['class']);
        self::assertSame('Database unavailable.', $record['context']['exception']['message']);
    }
}
