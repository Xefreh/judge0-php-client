<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Cache;

class ArrayCache implements CacheInterface
{
    /** @var array<string, array{value: mixed, expires: ?int}> */
    private array $cache = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->cache[$key]['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->cache[$key] = [
            'value' => $value,
            'expires' => $ttl !== null ? time() + $ttl : null,
        ];

        return true;
    }

    public function has(string $key): bool
    {
        if (!array_key_exists($key, $this->cache)) {
            return false;
        }

        $entry = $this->cache[$key];
        if ($entry['expires'] !== null && $entry['expires'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];
        return true;
    }
}
