<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\DTO\Status;

class StatusTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = ['id' => 3, 'description' => 'Accepted'];
        $status = Status::fromArray($data);

        $this->assertEquals(3, $status->id);
        $this->assertEquals('Accepted', $status->description);
    }

    public function testToArray(): void
    {
        $status = new Status(id: 3, description: 'Accepted');
        $array = $status->toArray();

        $this->assertEquals(['id' => 3, 'description' => 'Accepted'], $array);
    }

    public function testIsPending(): void
    {
        $inQueue = new Status(id: Status::IN_QUEUE, description: 'In Queue');
        $processing = new Status(id: Status::PROCESSING, description: 'Processing');
        $accepted = new Status(id: Status::ACCEPTED, description: 'Accepted');

        $this->assertTrue($inQueue->isPending());
        $this->assertTrue($processing->isPending());
        $this->assertFalse($accepted->isPending());
    }

    public function testIsSuccess(): void
    {
        $accepted = new Status(id: Status::ACCEPTED, description: 'Accepted');
        $wrongAnswer = new Status(id: Status::WRONG_ANSWER, description: 'Wrong Answer');

        $this->assertTrue($accepted->isSuccess());
        $this->assertFalse($wrongAnswer->isSuccess());
    }

    public function testIsError(): void
    {
        $accepted = new Status(id: Status::ACCEPTED, description: 'Accepted');
        $wrongAnswer = new Status(id: Status::WRONG_ANSWER, description: 'Wrong Answer');
        $timeLimitExceeded = new Status(id: Status::TIME_LIMIT_EXCEEDED, description: 'Time Limit Exceeded');

        $this->assertFalse($accepted->isError());
        $this->assertTrue($wrongAnswer->isError());
        $this->assertTrue($timeLimitExceeded->isError());
    }

    public function testStatusConstants(): void
    {
        $this->assertEquals(1, Status::IN_QUEUE);
        $this->assertEquals(2, Status::PROCESSING);
        $this->assertEquals(3, Status::ACCEPTED);
        $this->assertEquals(4, Status::WRONG_ANSWER);
        $this->assertEquals(5, Status::TIME_LIMIT_EXCEEDED);
        $this->assertEquals(6, Status::COMPILATION_ERROR);
    }
}
