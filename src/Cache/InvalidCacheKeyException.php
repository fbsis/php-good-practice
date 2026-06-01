<?php

declare(strict_types=1);

namespace Challenge\Cache;

use InvalidArgumentException;
use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

final class InvalidCacheKeyException extends InvalidArgumentException implements PsrInvalidArgumentException
{
}
