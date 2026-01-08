<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\Cache\ArrayCache;
use Xefreh\Judge0PhpClient\DTO\Submission;
use Xefreh\Judge0PhpClient\Judge0Client;

/**
 * Integration tests for Judge0Client.
 *
 * These tests run against the real Judge0 API.
 * Set JUDGE0_API_HOST environment variable to run these tests.
 * If JUDGE0_API_KEY is not set, tests will use the free plan.
 */
class Judge0ClientTest extends TestCase
{
    private ?Judge0Client $client = null;

    protected function setUp(): void
    {
        $apiHost = getenv('JUDGE0_API_HOST');
        if (!$apiHost) {
            $this->markTestSkipped('JUDGE0_API_HOST environment variable not set');
        }

        $apiKey = getenv('JUDGE0_API_KEY') ?: null;

        $this->client = new Judge0Client(
            apiHost: $apiHost,
            apiKey: $apiKey,
            cache: new ArrayCache(),
        );
    }

    public function testGetLanguages(): void
    {
        $languages = $this->client->languages->all();

        $this->assertNotEmpty($languages);
        $this->assertIsInt($languages[0]->id);
        $this->assertIsString($languages[0]->name);
    }

    public function testGetLanguageById(): void
    {
        $language = $this->client->languages->get(71); // Python 3.8.1

        $this->assertEquals(71, $language->id);
        $this->assertStringContainsString('Python', $language->name);
    }

    public function testGetStatuses(): void
    {
        $statuses = $this->client->system->statuses();

        $this->assertNotEmpty($statuses);
        $this->assertGreaterThanOrEqual(14, count($statuses));
    }

    public function testGetAbout(): void
    {
        $about = $this->client->system->about();

        $this->assertNotEmpty($about->version);
        $this->assertStringContainsString('judge0', $about->homepage);
    }

    public function testGetConfig(): void
    {
        $config = $this->client->system->config();

        $this->assertIsBool($config->maintenanceMode);
        $this->assertIsFloat($config->cpuTimeLimit);
        $this->assertIsInt($config->memoryLimit);
    }

    public function testCreateAndGetSubmission(): void
    {
        $submission = new Submission(
            languageId: 71, // Python 3.8.1
            sourceCode: 'print("Hello, Judge0!")',
        );

        $result = $this->client->submissions->create($submission);

        $this->assertNotEmpty($result->token);

        // Wait for result
        $finalResult = $this->client->submissions->wait($result->token, maxAttempts: 30);

        $this->assertFalse($finalResult->isPending());
        $this->assertEquals('Hello, Judge0!', trim($finalResult->stdout ?? ''));
    }

    public function testSubmissionWithStdin(): void
    {
        $sourceCode = <<<'PYTHON'
name = input()
print(f"Hello, {name}!")
PYTHON;

        $submission = new Submission(
            languageId: 71, // Python 3.8.1
            sourceCode: $sourceCode,
            stdin: 'World',
        );

        $result = $this->client->submissions->create($submission, wait: true);

        // If wait is supported, result should be final
        if (!$result->isPending()) {
            $this->assertEquals('Hello, World!', trim($result->stdout ?? ''));
        } else {
            // Otherwise wait for it
            $finalResult = $this->client->submissions->wait($result->token);
            $this->assertEquals('Hello, World!', trim($finalResult->stdout ?? ''));
        }
    }

    public function testCachingWorks(): void
    {
        // First call should hit the API
        $languages1 = $this->client->languages->all();

        // Second call should hit the cache
        $languages2 = $this->client->languages->all();

        $this->assertEquals($languages1, $languages2);
    }

    public function testClearCache(): void
    {
        // Populate cache
        $this->client->languages->all();

        // Clear cache
        $this->client->clearCache();

        // This should hit the API again (no error means success)
        $languages = $this->client->languages->all();
        $this->assertNotEmpty($languages);
    }
}
