# Judge0 PHP Client

A PHP client library for the [Judge0](https://judge0.com) API - an online code execution system that supports 60+
programming languages.

## Requirements

- PHP 8.4 or higher
- Composer
- ext-zip (for multi-file submissions)

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

| Parameter | Type           | Required | Description                                        |
|-----------|----------------|----------|----------------------------------------------------|
| `apiHost` | string         | Yes      | Judge0 API host (e.g., `judge0-ce.p.rapidapi.com`) |
| `apiKey`  | string         | Yes      | API key for authentication                         |
| `cache`   | CacheInterface | No       | Cache instance for reducing API calls              |

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

### Multi-file Programs

For projects with multiple files (e.g., C++ with CMake), use the `ArchiveBuilder` utility to create a base64-encoded zip archive:

```php
use Xefreh\Judge0PhpClient\Utils\ArchiveBuilder;
use Xefreh\Judge0PhpClient\DTO\Submission;

// From string contents
$archive = ArchiveBuilder::createArchive(
    files: [
        'main.cpp' => '#include <iostream>\nint main() { std::cout << "Hello"; }',
        'CMakeLists.txt' => 'cmake_minimum_required(VERSION 3.10)...',
    ],
    runScript: "#!/bin/bash\n./build/main",
    compileScript: "#!/bin/bash\nmkdir build && cd build && cmake .. && make",
);

// Or from file paths on disk
$archive = ArchiveBuilder::createArchiveFromFiles(
    files: [
        'main.cpp' => '/path/to/project/main.cpp',
        'utils/helper.cpp' => '/path/to/project/utils/helper.cpp',
        'CMakeLists.txt' => '/path/to/project/CMakeLists.txt',
    ],
    runScript: "#!/bin/bash\n./build/main",
    compileScript: "#!/bin/bash\nmkdir build && cd build && cmake .. && make",
);

$submission = new Submission(
    languageId: 54, // C++ (GCC)
    additionalFiles: $archive,
);

$result = $client->submissions->create($submission, wait: true);
```

**Notes:**
- The `run` script is required and tells Judge0 how to execute your program
- The `compile` script is optional (omit for interpreted languages)
- Both scripts must be valid bash scripts
- The archive key defines the relative path inside the sandbox (e.g., `utils/helper.cpp`)

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

For a complete list of supported languages and their IDs, use `$client->languages->all()` or visit the [Judge0 CE API reference](https://rapidapi.com/judge0-official/api/judge0-ce) on RapidAPI.

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
