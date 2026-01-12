<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\Exceptions\ConfigException;
use Xefreh\Judge0PhpClient\Judge0Client;

class Judge0ClientTest extends TestCase
{
    public function testThrowsExceptionWhenApiHostIsNull(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Judge0 API host is required');

        new Judge0Client(apiHost: null);
    }

    public function testThrowsExceptionWhenApiHostIsEmpty(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Judge0 API host is required');

        new Judge0Client(apiHost: '');
    }

    public function testThrowsExceptionWhenApiKeyIsNull(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Judge0 API key is required');

        new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
            apiKey: null,
        );
    }

    public function testThrowsExceptionWhenApiKeyIsEmpty(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Judge0 API key is required');

        new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
            apiKey: '',
        );
    }

    public function testAcceptsValidConfiguration(): void
    {
        $client = new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
            apiKey: 'test-api-key',
        );

        $this->assertInstanceOf(Judge0Client::class, $client);
    }
}
