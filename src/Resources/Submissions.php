<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Resources;

use Xefreh\Judge0PhpClient\DTO\Status;
use Xefreh\Judge0PhpClient\DTO\Submission;
use Xefreh\Judge0PhpClient\DTO\SubmissionResult;
use Xefreh\Judge0PhpClient\Exceptions\ApiException;
use Xefreh\Judge0PhpClient\Http\HttpClient;

class Submissions
{
    private const FINAL_RESULT_CACHE_TTL = 86400; // 24 hours for final results

    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    /**
     * Create a new submission.
     *
     * @throws ApiException
     */
    public function create(Submission $submission, bool $wait = false): SubmissionResult
    {
        $query = [
            'base64_encoded' => 'true',
            'fields' => '*',
        ];

        if ($wait) {
            $query['wait'] = 'true';
        }

        $response = $this->http->post('/submissions', $submission->toArray(true), $query);

        return SubmissionResult::fromArray($response, true);
    }

    /**
     * Get submission result by token.
     *
     * @throws ApiException
     */
    public function get(string $token): SubmissionResult
    {
        $query = [
            'base64_encoded' => 'true',
            'fields' => '*',
        ];

        $response = $this->http->get("/submissions/{$token}", $query);
        $result = SubmissionResult::fromArray($response, true);

        // Cache final results (not pending)
        if (!$result->isPending() && $this->http->getCache() !== null) {
            $cacheKey = "submission:{$token}";
            $this->http->getCache()->set($cacheKey, $response, self::FINAL_RESULT_CACHE_TTL);
        }

        return $result;
    }

    /**
     * Create multiple submissions in batch.
     *
     * @param Submission[] $submissions
     * @return SubmissionResult[]
     * @throws ApiException
     */
    public function createBatch(array $submissions): array
    {
        $query = [
            'base64_encoded' => 'true',
        ];

        $data = [
            'submissions' => array_map(
                fn(Submission $s) => $s->toArray(true),
                $submissions
            ),
        ];

        $response = $this->http->post('/submissions/batch', $data, $query);

        return array_map(
            fn(array $data) => SubmissionResult::fromArray($data, true),
            $response
        );
    }

    /**
     * Get multiple submissions by tokens.
     *
     * @param string[] $tokens
     * @return SubmissionResult[]
     * @throws ApiException
     */
    public function getBatch(array $tokens): array
    {
        $query = [
            'tokens' => implode(',', $tokens),
            'base64_encoded' => 'true',
            'fields' => '*',
        ];

        $response = $this->http->get('/submissions/batch', $query);

        $submissions = $response['submissions'] ?? $response;

        return array_map(
            fn(array $data) => SubmissionResult::fromArray($data, true),
            $submissions
        );
    }

    /**
     * Wait for a submission to complete by polling.
     *
     * @throws ApiException
     */
    public function wait(string $token, int $maxAttempts = 30, int $intervalMs = 1000): SubmissionResult
    {
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $result = $this->get($token);

            if (!$result->isPending()) {
                return $result;
            }

            $attempts++;
            usleep($intervalMs * 1000);
        }

        return $this->get($token);
    }
}
