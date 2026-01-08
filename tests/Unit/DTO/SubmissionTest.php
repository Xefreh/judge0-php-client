<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\DTO\Submission;

class SubmissionTest extends TestCase
{
    public function testToArrayWithBase64Encoding(): void
    {
        $submission = new Submission(
            languageId: 71,
            sourceCode: 'print("Hello")',
        );

        $array = $submission->toArray(true);

        $this->assertEquals(71, $array['language_id']);
        $this->assertEquals(base64_encode('print("Hello")'), $array['source_code']);
    }

    public function testToArrayWithoutBase64Encoding(): void
    {
        $submission = new Submission(
            languageId: 71,
            sourceCode: 'print("Hello")',
        );

        $array = $submission->toArray(false);

        $this->assertEquals(71, $array['language_id']);
        $this->assertEquals('print("Hello")', $array['source_code']);
    }

    public function testToArrayWithStdin(): void
    {
        $submission = new Submission(
            languageId: 71,
            sourceCode: 'print(input())',
            stdin: 'test input',
        );

        $array = $submission->toArray(true);

        $this->assertEquals(base64_encode('test input'), $array['stdin']);
    }

    public function testToArrayWithAllOptions(): void
    {
        $submission = new Submission(
            languageId: 71,
            sourceCode: 'print("Hello")',
            stdin: 'input',
            expectedOutput: 'Hello',
            cpuTimeLimit: 5.0,
            cpuExtraTime: 1.0,
            wallTimeLimit: 10.0,
            memoryLimit: 128000,
            stackLimit: 64000,
            compilerOptions: '-O2',
            commandLineArguments: '--verbose',
            callbackUrl: 'https://example.com/callback',
            redirectStderrToStdout: true,
        );

        $array = $submission->toArray(false);

        $this->assertEquals(71, $array['language_id']);
        $this->assertEquals('print("Hello")', $array['source_code']);
        $this->assertEquals('input', $array['stdin']);
        $this->assertEquals('Hello', $array['expected_output']);
        $this->assertEquals(5.0, $array['cpu_time_limit']);
        $this->assertEquals(1.0, $array['cpu_extra_time']);
        $this->assertEquals(10.0, $array['wall_time_limit']);
        $this->assertEquals(128000, $array['memory_limit']);
        $this->assertEquals(64000, $array['stack_limit']);
        $this->assertEquals('-O2', $array['compiler_options']);
        $this->assertEquals('--verbose', $array['command_line_arguments']);
        $this->assertEquals('https://example.com/callback', $array['callback_url']);
        $this->assertTrue($array['redirect_stderr_to_stdout']);
    }

    public function testToArrayExcludesNullValues(): void
    {
        $submission = new Submission(
            languageId: 71,
            sourceCode: 'print("Hello")',
        );

        $array = $submission->toArray(false);

        $this->assertArrayNotHasKey('stdin', $array);
        $this->assertArrayNotHasKey('expected_output', $array);
        $this->assertArrayNotHasKey('cpu_time_limit', $array);
    }
}
