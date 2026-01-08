<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Xefreh\Judge0PhpClient\Cache\CacheInterface;
use Xefreh\Judge0PhpClient\Exceptions\ApiException;

class HttpClient
{
    private Client $client;

    public function __construct(
        private readonly string $apiHost,
        private readonly ?string $apiKey = null,
        private readonly ?CacheInterface $cache = null,
    ) {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->apiKey !== null) {
            $headers['X-RapidAPI-Key'] = $this->apiKey;
            $headers['X-RapidAPI-Host'] = $this->apiHost;
        }

        $this->client = new Client([
            'base_uri' => "https://{$this->apiHost}",
            'headers' => $headers,
        ]);
    }

    /**
     * @throws ApiException
     */
    public function get(string $endpoint, array $query = [], ?int $cacheTtl = null): array
    {
        $cacheKey = $this->buildCacheKey('GET', $endpoint, $query);

        if ($this->cache !== null && $cacheTtl !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $response = $this->request('GET', $endpoint, ['query' => $query]);

        if ($this->cache !== null && $cacheTtl !== null) {
            $this->cache->set($cacheKey, $response, $cacheTtl);
        }

        return $response;
    }

    /**
     * @throws ApiException
     */
    public function post(string $endpoint, array $data = [], array $query = []): array
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
            'query' => $query,
        ]);
    }

    /**
     * @throws ApiException
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            $body = $response->getBody()->getContents();

            return json_decode($body, true) ?? [];
        } catch (GuzzleException $e) {
            $statusCode = 0;
            $responseBody = null;

            if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
                $statusCode = $e->getResponse()->getStatusCode();
                $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
            }

            throw new ApiException(
                message: $e->getMessage(),
                statusCode: $statusCode,
                responseBody: $responseBody,
            );
        }
    }

    private function buildCacheKey(string $method, string $endpoint, array $params = []): string
    {
        $key = "{$method}:{$endpoint}";
        if (!empty($params)) {
            $key .= ':' . md5(json_encode($params));
        }
        return $key;
    }

    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    public function clearCache(): void
    {
        $this->cache?->clear();
    }
}
