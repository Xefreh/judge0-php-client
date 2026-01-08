<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\DTO\Status;
use Xefreh\Judge0PhpClient\DTO\SubmissionResult;

class SubmissionResultTest extends TestCase
{
    public function testFromArrayWithBase64EncodedData(): void
    {
        $data = [
            'token' => 'abc123',
            'status' => ['id' => 3, 'description' => 'Accepted'],
            'stdout' => base64_encode('Hello World'),
            'stderr' => null,
            'compile_output' => null,
            'time' => '0.01',
            'memory' => 1024,
        ];

        $result = SubmissionResult::fromArray($data, true);

        $this->assertEquals('abc123', $result->token);
        $this->assertEquals(3, $result->status->id);
        $this->assertEquals('Hello World', $result->stdout);
        $this->assertEquals(0.01, $result->time);
        $this->assertEquals(1024, $result->memory);
    }

    public function testFromArrayWithPlainTextData(): void
    {
        $data = [
            'token' => 'abc123',
            'status' => ['id' => 3, 'description' => 'Accepted'],
            'stdout' => 'Hello World',
        ];

        $result = SubmissionResult::fromArray($data, false);

        $this->assertEquals('Hello World', $result->stdout);
    }

    public function testIsPending(): void
    {
        $pendingResult = SubmissionResult::fromArray([
            'token' => 'abc123',
            'status' => ['id' => Status::IN_QUEUE, 'description' => 'In Queue'],
        ], false);

        $completedResult = SubmissionResult::fromArray([
            'token' => 'abc123',
            'status' => ['id' => Status::ACCEPTED, 'description' => 'Accepted'],
        ], false);

        $this->assertTrue($pendingResult->isPending());
        $this->assertFalse($completedResult->isPending());
    }

    public function testIsSuccess(): void
    {
        $successResult = SubmissionResult::fromArray([
            'token' => 'abc123',
            'status' => ['id' => Status::ACCEPTED, 'description' => 'Accepted'],
        ], false);

        $errorResult = SubmissionResult::fromArray([
            'token' => 'abc123',
            'status' => ['id' => Status::WRONG_ANSWER, 'description' => 'Wrong Answer'],
        ], false);

        $this->assertTrue($successResult->isSuccess());
        $this->assertFalse($errorResult->isSuccess());
    }

    public function testIsError(): void
    {
        $errorResult = SubmissionResult::fromArray([
            'token' => 'abc123',
            'status' => ['id' => Status::COMPILATION_ERROR, 'description' => 'Compilation Error'],
        ], false);

        $successResult = SubmissionResult::fromArray([
            'token' => 'abc123',
            'status' => ['id' => Status::ACCEPTED, 'description' => 'Accepted'],
        ], false);

        $this->assertTrue($errorResult->isError());
        $this->assertFalse($successResult->isError());
    }

    public function testToArray(): void
    {
        $result = new SubmissionResult(
            token: 'abc123',
            status: new Status(id: 3, description: 'Accepted'),
            stdout: 'Hello',
            time: 0.01,
            memory: 1024,
        );

        $array = $result->toArray();

        $this->assertEquals('abc123', $array['token']);
        $this->assertEquals(['id' => 3, 'description' => 'Accepted'], $array['status']);
        $this->assertEquals('Hello', $array['stdout']);
        $this->assertEquals(0.01, $array['time']);
        $this->assertEquals(1024, $array['memory']);
    }
}
