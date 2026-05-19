<?php

declare(strict_types=1);

namespace Challenge\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class MemoryCacheItemPool implements CacheItemPoolInterface
{
    private const INVALID_KEY_PATTERN = '/[{}()\/\\\\@:]/';

    /** @var array<string, MemoryCacheItem> */
    private array $items = [];

    /** @var array<string, MemoryCacheItem> */
    private array $deferred = [];

    public function getItem(string $key): CacheItemInterface
    {
        $this->validateKey($key);

        if (!isset($this->items[$key]) || $this->items[$key]->isExpired()) {
            unset($this->items[$key]);

            return new MemoryCacheItem($key);
        }

        return clone $this->items[$key];
    }

    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->getItem($key);
        }
    }

    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function clear(): bool
    {
        $this->items = [];
        $this->deferred = [];

        return true;
    }

    public function deleteItem(string $key): bool
    {
        $this->validateKey($key);
        unset($this->items[$key], $this->deferred[$key]);

        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof MemoryCacheItem) {
            return false;
        }

        $this->validateKey($item->getKey());
        $this->items[$item->getKey()] = clone $item;

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        if (!$item instanceof MemoryCacheItem) {
            return false;
        }

        $this->validateKey($item->getKey());
        $this->deferred[$item->getKey()] = clone $item;

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->items[$item->getKey()] = clone $item;
        }

        $this->deferred = [];

        return true;
    }

    private function validateKey(string $key): void
    {
        if ($key === '' || preg_match(self::INVALID_KEY_PATTERN, $key) === 1) {
            throw new InvalidCacheKeyException('Cache key contains reserved characters or is empty.');
        }
    }
}
