<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit\Cache;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\Cache\ArrayCache;

class ArrayCacheTest extends TestCase
{
    private ArrayCache $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayCache();
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('key', 'value');
        $this->assertEquals('value', $this->cache->get('key'));
    }

    public function testGetReturnsDefaultWhenKeyNotFound(): void
    {
        $this->assertEquals('default', $this->cache->get('nonexistent', 'default'));
        $this->assertNull($this->cache->get('nonexistent'));
    }

    public function testHasReturnsTrueWhenKeyExists(): void
    {
        $this->cache->set('key', 'value');
        $this->assertTrue($this->cache->has('key'));
    }

    public function testHasReturnsFalseWhenKeyDoesNotExist(): void
    {
        $this->assertFalse($this->cache->has('nonexistent'));
    }

    public function testDelete(): void
    {
        $this->cache->set('key', 'value');
        $this->assertTrue($this->cache->has('key'));

        $this->cache->delete('key');
        $this->assertFalse($this->cache->has('key'));
    }

    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');

        $this->cache->clear();

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }

    public function testTtlExpiration(): void
    {
        $this->cache->set('key', 'value', 1);
        $this->assertTrue($this->cache->has('key'));

        sleep(2);

        $this->assertFalse($this->cache->has('key'));
        $this->assertNull($this->cache->get('key'));
    }

    public function testNullTtlDoesNotExpire(): void
    {
        $this->cache->set('key', 'value', null);
        $this->assertTrue($this->cache->has('key'));
        $this->assertEquals('value', $this->cache->get('key'));
    }

    public function testCanStoreArrays(): void
    {
        $array = ['foo' => 'bar', 'baz' => [1, 2, 3]];
        $this->cache->set('array', $array);
        $this->assertEquals($array, $this->cache->get('array'));
    }

    public function testCanStoreObjects(): void
    {
        $object = new \stdClass();
        $object->name = 'test';
        $this->cache->set('object', $object);
        $this->assertEquals($object, $this->cache->get('object'));
    }
}
