# Judge0 PHP Client

A PHP client library for the [Judge0](https://judge0.com) API - an online code execution system that supports 60+
programming languages.

## Requirements

- PHP 8.4 or higher
- Composer

## Installation

```bash
composer require xefreh/judge0-php-client
```

## Quick Start

```php
use Xefreh\Judge0PhpClient\Judge0Client;
use Xefreh\Judge0PhpClient\DTO\Submission;

$client = new Judge0Client(
    apiHost: 'judge0-ce.p.rapidapi.com',
    apiKey: 'your-api-key',
);

// Execute Python code
$submission = new Submission(
    languageId: 71, // Python 3.8.1
    sourceCode: 'print("Hello, World!")',
);

$result = $client->submissions->create($submission);
$final = $client->submissions->wait($result->token);

echo $final->stdout; // "Hello, World!"
```

## Configuration

### Constructor Parameters

| Parameter     | Type           | Required | Description                                        |
|---------------|----------------|----------|----------------------------------------------------|
| `apiHost`     | string         | Yes      | Judge0 API host (e.g., `judge0-ce.p.rapidapi.com`) |
| `apiKey`      | string         | No*      | API key for authentication                         |
| `cache`       | CacheInterface | No       | Cache instance for reducing API calls              |
| `environment` | Environment    | No       | `Development` (default) or `Production`            |

*API key is required when `environment` is set to `Production`.

### Environment Modes

```php
use Xefreh\Judge0PhpClient\Enums\Environment;

// Development (default) - API key is optional
$client = new Judge0Client(
    apiHost: 'judge0-ce.p.rapidapi.com',
    environment: Environment::Development,
);

// Production - API key is required, throws ConfigException if missing
$client = new Judge0Client(
    apiHost: 'judge0-ce.p.rapidapi.com',
    apiKey: 'your-api-key',
    environment: Environment::Production,
);
```

### Caching

Enable in-memory caching to reduce API calls:

```php
use Xefreh\Judge0PhpClient\Cache\ArrayCache;

$client = new Judge0Client(
    apiHost: 'judge0-ce.p.rapidapi.com',
    apiKey: 'your-api-key',
    cache: new ArrayCache(),
);

// Clear cache when needed
$client->clearCache();
```

**Cache TTLs:**

- Languages & Statuses: 24 hours
- About & Config: 1 hour
- Completed submission results: 24 hours

## API Reference

### Languages

```php
// Get all available languages
$languages = $client->languages->all();

// Get a specific language
$python = $client->languages->get(71);
echo $python->name; // "Python (3.8.1)"
```

### Submissions

```php
use Xefreh\Judge0PhpClient\DTO\Submission;

// Create a submission
$submission = new Submission(
    languageId: 71,
    sourceCode: 'print(input())',
    stdin: 'Hello',
);

// Async submission (returns immediately with token)
$result = $client->submissions->create($submission);

// Sync submission (waits for result)
$result = $client->submissions->create($submission, wait: true);

// Get submission by token
$result = $client->submissions->get($token);

// Wait for submission to complete (polling)
$result = $client->submissions->wait($token, maxAttempts: 30, intervalMs: 1000);

// Batch submissions
$results = $client->submissions->createBatch([$submission1, $submission2]);
$results = $client->submissions->getBatch(['token1', 'token2']);
```

### Submission Options

```php
$submission = new Submission(
    languageId: 71,
    sourceCode: 'print("Hello")',
    stdin: 'input data',
    expectedOutput: 'Hello',
    cpuTimeLimit: 5.0,
    cpuExtraTime: 1.0,
    wallTimeLimit: 10.0,
    memoryLimit: 128000,
    stackLimit: 64000,
    compilerOptions: '-O2',
    commandLineArguments: '--verbose',
    callbackUrl: 'https://example.com/callback',
    redirectStderrToStdout: false,
);
```

### Submission Result

```php
$result = $client->submissions->wait($token);

$result->token;        // Submission token
$result->status;       // Status object (id, description)
$result->stdout;       // Standard output
$result->stderr;       // Standard error
$result->compileOutput; // Compilation output
$result->message;      // Judge0 message
$result->time;         // Execution time (seconds)
$result->memory;       // Memory used (KB)
$result->exitCode;     // Exit code

// Status helpers
$result->isPending();  // true if still processing
$result->isSuccess();  // true if accepted
$result->isError();    // true if error occurred
```

### System

```php
// Get API information
$about = $client->system->about();
echo $about->version; // "1.13.1"

// Get API configuration
$config = $client->system->config();
echo $config->cpuTimeLimit; // 5.0

// Get all submission statuses
$statuses = $client->system->statuses();
```

## Language IDs

Common language IDs:

| ID | Language                     |
|----|------------------------------|
| 50 | C (GCC 9.2.0)                |
| 54 | C++ (GCC 9.2.0)              |
| 62 | Java (OpenJDK 13.0.1)        |
| 63 | JavaScript (Node.js 12.14.0) |
| 71 | Python (3.8.1)               |
| 72 | Ruby (2.7.0)                 |
| 73 | Rust (1.40.0)                |
| 74 | TypeScript (3.7.4)           |
| 82 | SQL (SQLite 3.27.2)          |

Use `$client->languages->all()` to get the full list.

## Error Handling

```php
use Xefreh\Judge0PhpClient\Exceptions\ConfigException;
use Xefreh\Judge0PhpClient\Exceptions\ApiException;

try {
    $client = new Judge0Client(apiHost: null);
} catch (ConfigException $e) {
    // Configuration error (missing host, missing API key in production)
    echo $e->getMessage();
}

try {
    $result = $client->submissions->get('invalid-token');
} catch (ApiException $e) {
    echo $e->getMessage();
    echo $e->getStatusCode();    // HTTP status code
    echo $e->getResponseBody();  // API response body
}
```

## Testing

```bash
# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run integration tests (requires API credentials)
JUDGE0_API_HOST=judge0-ce.p.rapidapi.com JUDGE0_API_KEY=your-key composer test:integration
```

## License

MIT
