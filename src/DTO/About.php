<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\DTO;

readonly class About
{
    public function __construct(
        public string $version,
        public string $homepage,
        public string $sourceCode,
        public string $maintainer,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            version: $data['version'],
            homepage: $data['homepage'],
            sourceCode: $data['source_code'],
            maintainer: $data['maintainer'],
        );
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'homepage' => $this->homepage,
            'source_code' => $this->sourceCode,
            'maintainer' => $this->maintainer,
        ];
    }
}
