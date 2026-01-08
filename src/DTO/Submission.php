<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\DTO;

readonly class Submission
{
    public function __construct(
        public int $languageId,
        public string $sourceCode,
        public ?string $stdin = null,
        public ?string $expectedOutput = null,
        public ?float $cpuTimeLimit = null,
        public ?float $cpuExtraTime = null,
        public ?float $wallTimeLimit = null,
        public ?int $memoryLimit = null,
        public ?int $stackLimit = null,
        public ?string $compilerOptions = null,
        public ?string $commandLineArguments = null,
        public ?string $callbackUrl = null,
        public ?bool $redirectStderrToStdout = null,
    ) {
    }

    public function toArray(bool $base64Encode = true): array
    {
        $data = [
            'language_id' => $this->languageId,
            'source_code' => $base64Encode ? base64_encode($this->sourceCode) : $this->sourceCode,
        ];

        if ($this->stdin !== null) {
            $data['stdin'] = $base64Encode ? base64_encode($this->stdin) : $this->stdin;
        }

        if ($this->expectedOutput !== null) {
            $data['expected_output'] = $base64Encode ? base64_encode($this->expectedOutput) : $this->expectedOutput;
        }

        if ($this->cpuTimeLimit !== null) {
            $data['cpu_time_limit'] = $this->cpuTimeLimit;
        }

        if ($this->cpuExtraTime !== null) {
            $data['cpu_extra_time'] = $this->cpuExtraTime;
        }

        if ($this->wallTimeLimit !== null) {
            $data['wall_time_limit'] = $this->wallTimeLimit;
        }

        if ($this->memoryLimit !== null) {
            $data['memory_limit'] = $this->memoryLimit;
        }

        if ($this->stackLimit !== null) {
            $data['stack_limit'] = $this->stackLimit;
        }

        if ($this->compilerOptions !== null) {
            $data['compiler_options'] = $this->compilerOptions;
        }

        if ($this->commandLineArguments !== null) {
            $data['command_line_arguments'] = $this->commandLineArguments;
        }

        if ($this->callbackUrl !== null) {
            $data['callback_url'] = $this->callbackUrl;
        }

        if ($this->redirectStderrToStdout !== null) {
            $data['redirect_stderr_to_stdout'] = $this->redirectStderrToStdout;
        }

        return $data;
    }
}
