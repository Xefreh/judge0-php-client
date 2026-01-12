<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient;

use Xefreh\Judge0PhpClient\Cache\CacheInterface;
use Xefreh\Judge0PhpClient\Exceptions\ConfigException;
use Xefreh\Judge0PhpClient\Http\HttpClient;
use Xefreh\Judge0PhpClient\Resources\Languages;
use Xefreh\Judge0PhpClient\Resources\Submissions;
use Xefreh\Judge0PhpClient\Resources\System;

class Judge0Client
{
    private HttpClient $http;
    public readonly Languages $languages;
    public readonly Submissions $submissions;
    public readonly System $system;

    /**
     * @throws ConfigException
     */
    public function __construct(
        ?string         $apiHost = null,
        ?string         $apiKey = null,
        ?CacheInterface $cache = null,
    )
    {
        $this->validateConfiguration($apiHost, $apiKey);

        $this->http = new HttpClient($apiHost, $apiKey, $cache);
        $this->languages = new Languages($this->http);
        $this->submissions = new Submissions($this->http);
        $this->system = new System($this->http);
    }

    /**
     * @throws ConfigException
     */
    private function validateConfiguration(?string $apiHost, ?string $apiKey): void
    {
        if ($apiHost === null || $apiHost === '') {
            throw new ConfigException(
                'Judge0 API host is required. Please set the JUDGE0_API_HOST environment variable or pass it to the constructor.'
            );
        }

        if ($apiKey === null || $apiKey === '') {
            throw new ConfigException(
                'Judge0 API key is required. Please set the JUDGE0_API_KEY environment variable or pass it to the constructor.'
            );
        }
    }

    public function clearCache(): void
    {
        $this->http->clearCache();
    }
}
