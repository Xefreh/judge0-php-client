<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Cache;

interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    public function has(string $key): bool;

    public function delete(string $key): bool;

    public function clear(): bool;
}
