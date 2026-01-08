<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\Enums\Environment;
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

    public function testThrowsExceptionWhenApiKeyMissingInProduction(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Judge0 API key is required in production environment');

        new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
            apiKey: null,
            environment: Environment::Production,
        );
    }

    public function testThrowsExceptionWhenApiKeyEmptyInProduction(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Judge0 API key is required in production environment');

        new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
            apiKey: '',
            environment: Environment::Production,
        );
    }

    public function testAllowsNullApiKeyInDevelopment(): void
    {
        $client = new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
            apiKey: null,
            environment: Environment::Development,
        );

        $this->assertInstanceOf(Judge0Client::class, $client);
    }

    public function testDefaultEnvironmentIsDevelopment(): void
    {
        $client = new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
        );

        $this->assertInstanceOf(Judge0Client::class, $client);
    }

    public function testAcceptsValidConfigurationInProduction(): void
    {
        $client = new Judge0Client(
            apiHost: 'judge0-ce.p.rapidapi.com',
            apiKey: 'test-api-key',
            environment: Environment::Production,
        );

        $this->assertInstanceOf(Judge0Client::class, $client);
    }
}
