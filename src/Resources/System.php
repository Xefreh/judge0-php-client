<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Resources;

use Xefreh\Judge0PhpClient\DTO\About;
use Xefreh\Judge0PhpClient\DTO\Config;
use Xefreh\Judge0PhpClient\DTO\Status;
use Xefreh\Judge0PhpClient\Exceptions\ApiException;
use Xefreh\Judge0PhpClient\Http\HttpClient;

class System
{
    private const int ABOUT_CACHE_TTL = 3600; // 1 hour
    private const int CONFIG_CACHE_TTL = 3600; // 1 hour
    private const int STATUSES_CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private readonly HttpClient $http,
    )
    {
    }

    /**
     * Get API information (version, homepage, etc.).
     *
     * @throws ApiException
     */
    public function about(): About
    {
        $response = $this->http->get('/about', [], self::ABOUT_CACHE_TTL);

        return About::fromArray($response);
    }

    /**
     * Get API configuration.
     *
     * @throws ApiException
     */
    public function config(): Config
    {
        $response = $this->http->get('/config_info', [], self::CONFIG_CACHE_TTL);

        return Config::fromArray($response);
    }

    /**
     * Get all submission statuses.
     *
     * @return Status[]
     * @throws ApiException
     */
    public function statuses(): array
    {
        $response = $this->http->get('/statuses', [], self::STATUSES_CACHE_TTL);

        return array_map(
            fn(array $data) => Status::fromArray($data),
            $response
        );
    }
}
