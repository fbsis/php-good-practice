<?php

declare(strict_types=1);

namespace Challenge\Cache;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

final class MemoryCacheItem implements CacheItemInterface
{
    private DateTimeInterface|null $expiresAt = null;

    public function __construct(
        private readonly string $key,
        private bool $hit = false,
        private mixed $value = null,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->hit ? $this->value : null;
    }

    public function isHit(): bool
    {
        return $this->hit && !$this->isExpired();
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->hit = true;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAt = $expiration;

        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if ($time === null) {
            $this->expiresAt = null;

            return $this;
        }

        $now = new DateTimeImmutable();
        $this->expiresAt = is_int($time) ? $now->modify('+' . $time . ' seconds') : $now->add($time);

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= new DateTimeImmutable();
    }
}
