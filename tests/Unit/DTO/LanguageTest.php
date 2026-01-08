<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\DTO\Language;

class LanguageTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = ['id' => 71, 'name' => 'Python (3.8.1)'];
        $language = Language::fromArray($data);

        $this->assertEquals(71, $language->id);
        $this->assertEquals('Python (3.8.1)', $language->name);
    }

    public function testToArray(): void
    {
        $language = new Language(id: 71, name: 'Python (3.8.1)');
        $array = $language->toArray();

        $this->assertEquals(['id' => 71, 'name' => 'Python (3.8.1)'], $array);
    }
}
