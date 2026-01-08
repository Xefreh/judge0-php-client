<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Resources;

use Xefreh\Judge0PhpClient\DTO\Language;
use Xefreh\Judge0PhpClient\Exceptions\ApiException;
use Xefreh\Judge0PhpClient\Http\HttpClient;

class Languages
{
    private const CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    /**
     * Get all available languages.
     *
     * @return Language[]
     * @throws ApiException
     */
    public function all(): array
    {
        $response = $this->http->get('/languages', [], self::CACHE_TTL);

        return array_map(
            fn(array $data) => Language::fromArray($data),
            $response
        );
    }

    /**
     * Get a specific language by ID.
     *
     * @throws ApiException
     */
    public function get(int $id): Language
    {
        $response = $this->http->get("/languages/{$id}", [], self::CACHE_TTL);

        return Language::fromArray($response);
    }
}
