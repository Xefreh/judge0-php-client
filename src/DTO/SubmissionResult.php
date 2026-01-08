<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\DTO;

readonly class SubmissionResult
{
    public function __construct(
        public string  $token,
        public ?Status $status = null,
        public ?string $stdout = null,
        public ?string $stderr = null,
        public ?string $compileOutput = null,
        public ?string $message = null,
        public ?float  $time = null,
        public ?int    $memory = null,
        public ?float  $wallTime = null,
        public ?int    $exitCode = null,
        public ?int    $exitSignal = null,
        public ?string $createdAt = null,
        public ?string $finishedAt = null,
    )
    {
    }

    public static function fromArray(array $data, bool $base64Encoded = true): self
    {
        $status = null;
        if (isset($data['status'])) {
            $status = Status::fromArray($data['status']);
        }

        return new self(
            token: $data['token'],
            status: $status,
            stdout: self::decodeIfNeeded($data['stdout'] ?? null, $base64Encoded),
            stderr: self::decodeIfNeeded($data['stderr'] ?? null, $base64Encoded),
            compileOutput: self::decodeIfNeeded($data['compile_output'] ?? null, $base64Encoded),
            message: $data['message'] ?? null,
            time: isset($data['time']) ? (float)$data['time'] : null,
            memory: isset($data['memory']) ? (int)$data['memory'] : null,
            wallTime: isset($data['wall_time']) ? (float)$data['wall_time'] : null,
            exitCode: isset($data['exit_code']) ? (int)$data['exit_code'] : null,
            exitSignal: isset($data['exit_signal']) ? (int)$data['exit_signal'] : null,
            createdAt: $data['created_at'] ?? null,
            finishedAt: $data['finished_at'] ?? null,
        );
    }

    private static function decodeIfNeeded(?string $value, bool $base64Encoded): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($base64Encoded) {
            $decoded = base64_decode($value, true);
            return $decoded !== false ? $decoded : $value;
        }

        return $value;
    }

    public function isPending(): bool
    {
        return $this->status?->isPending() ?? true;
    }

    public function isSuccess(): bool
    {
        return $this->status?->isSuccess() ?? false;
    }

    public function isError(): bool
    {
        return $this->status?->isError() ?? false;
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'status' => $this->status?->toArray(),
            'stdout' => $this->stdout,
            'stderr' => $this->stderr,
            'compile_output' => $this->compileOutput,
            'message' => $this->message,
            'time' => $this->time,
            'memory' => $this->memory,
            'wall_time' => $this->wallTime,
            'exit_code' => $this->exitCode,
            'exit_signal' => $this->exitSignal,
            'created_at' => $this->createdAt,
            'finished_at' => $this->finishedAt,
        ];
    }
}
