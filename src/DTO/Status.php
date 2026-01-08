<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\DTO;

readonly class Status
{
    public const int IN_QUEUE = 1;
    public const int PROCESSING = 2;
    public const int ACCEPTED = 3;
    public const int WRONG_ANSWER = 4;
    public const int TIME_LIMIT_EXCEEDED = 5;
    public const int COMPILATION_ERROR = 6;
    public const int RUNTIME_ERROR_SIGSEGV = 7;
    public const int RUNTIME_ERROR_SIGXFSZ = 8;
    public const int RUNTIME_ERROR_SIGFPE = 9;
    public const int RUNTIME_ERROR_SIGABRT = 10;
    public const int RUNTIME_ERROR_NZEC = 11;
    public const int RUNTIME_ERROR_OTHER = 12;
    public const int INTERNAL_ERROR = 13;
    public const int EXEC_FORMAT_ERROR = 14;

    public function __construct(
        public int    $id,
        public string $description,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            description: $data['description'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
        ];
    }

    public function isPending(): bool
    {
        return $this->id === self::IN_QUEUE || $this->id === self::PROCESSING;
    }

    public function isSuccess(): bool
    {
        return $this->id === self::ACCEPTED;
    }

    public function isError(): bool
    {
        return $this->id >= self::WRONG_ANSWER;
    }
}
