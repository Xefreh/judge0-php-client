<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Exceptions;

class ApiException extends Judge0Exception
{
    public function __construct(
        string                  $message,
        private readonly int    $statusCode = 0,
        private readonly ?array $responseBody = null,
    )
    {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
