<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Tests\Unit\Utils;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Xefreh\Judge0PhpClient\Utils\ArchiveBuilder;
use ZipArchive;

class ArchiveBuilderTest extends TestCase
{
    public function testCreateArchiveWithRunScriptOnly(): void
    {
        $files = ['main.py' => 'print("Hello")'];
        $runScript = "#!/bin/bash\npython main.py";

        $result = ArchiveBuilder::createArchive($files, $runScript);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);

        // Verify it's valid base64
        $decoded = base64_decode($result, true);
        $this->assertNotFalse($decoded);

        // Verify archive contents
        $contents = $this->extractArchive($result);
        $this->assertArrayHasKey('run', $contents);
        $this->assertArrayHasKey('main.py', $contents);
        $this->assertArrayNotHasKey('compile', $contents);
        $this->assertEquals($runScript, $contents['run']);
        $this->assertEquals('print("Hello")', $contents['main.py']);
    }

    public function testCreateArchiveWithCompileScript(): void
    {
        $files = ['main.cpp' => '#include <iostream>'];
        $runScript = "#!/bin/bash\n./main";
        $compileScript = "#!/bin/bash\ng++ -o main main.cpp";

        $result = ArchiveBuilder::createArchive($files, $runScript, $compileScript);

        $contents = $this->extractArchive($result);
        $this->assertArrayHasKey('run', $contents);
        $this->assertArrayHasKey('compile', $contents);
        $this->assertArrayHasKey('main.cpp', $contents);
        $this->assertEquals($runScript, $contents['run']);
        $this->assertEquals($compileScript, $contents['compile']);
    }

    public function testCreateArchiveWithSubdirectories(): void
    {
        $files = [
            'main.cpp' => '#include "utils/helper.h"',
            'utils/helper.cpp' => 'void helper() {}',
            'utils/helper.h' => 'void helper();',
        ];
        $runScript = "#!/bin/bash\n./main";

        $result = ArchiveBuilder::createArchive($files, $runScript);

        $contents = $this->extractArchive($result);
        $this->assertArrayHasKey('main.cpp', $contents);
        $this->assertArrayHasKey('utils/helper.cpp', $contents);
        $this->assertArrayHasKey('utils/helper.h', $contents);
    }

    public function testCreateArchiveThrowsOnEmptyFiles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Files array cannot be empty');

        ArchiveBuilder::createArchive([], "#!/bin/bash\n./main");
    }

    public function testCreateArchiveThrowsOnEmptyRunScript(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Run script cannot be empty');

        ArchiveBuilder::createArchive(['main.py' => 'print("Hello")'], '');
    }

    public function testCreateArchiveThrowsOnWhitespaceOnlyRunScript(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Run script cannot be empty');

        ArchiveBuilder::createArchive(['main.py' => 'print("Hello")'], '   ');
    }

    public function testCreateArchiveFromFilesWithValidFiles(): void
    {
        $tempDir = sys_get_temp_dir() . '/judge0_test_' . uniqid();
        mkdir($tempDir);
        mkdir($tempDir . '/utils');

        $mainFile = $tempDir . '/main.py';
        $utilFile = $tempDir . '/utils/helper.py';

        file_put_contents($mainFile, 'print("Hello")');
        file_put_contents($utilFile, 'def helper(): pass');

        try {
            $result = ArchiveBuilder::createArchiveFromFiles(
                [
                    'main.py' => $mainFile,
                    'utils/helper.py' => $utilFile,
                ],
                "#!/bin/bash\npython main.py"
            );

            $contents = $this->extractArchive($result);
            $this->assertArrayHasKey('main.py', $contents);
            $this->assertArrayHasKey('utils/helper.py', $contents);
            $this->assertEquals('print("Hello")', $contents['main.py']);
            $this->assertEquals('def helper(): pass', $contents['utils/helper.py']);
        } finally {
            unlink($utilFile);
            unlink($mainFile);
            rmdir($tempDir . '/utils');
            rmdir($tempDir);
        }
    }

    public function testCreateArchiveFromFilesThrowsOnNonexistentFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');

        ArchiveBuilder::createArchiveFromFiles(
            ['main.py' => '/nonexistent/path/main.py'],
            "#!/bin/bash\npython main.py"
        );
    }

    public function testCreateArchiveFromFilesThrowsOnEmptyFiles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Files array cannot be empty');

        ArchiveBuilder::createArchiveFromFiles([], "#!/bin/bash\n./main");
    }

    /**
     * Extracts archive contents from base64 string
     *
     * @return array<string, string>
     */
    private function extractArchive(string $base64Archive): array
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_archive_');
        file_put_contents($tempFile, base64_decode($base64Archive));

        $zip = new ZipArchive();
        $zip->open($tempFile);

        $contents = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $contents[$name] = $zip->getFromIndex($i);
        }

        $zip->close();
        unlink($tempFile);

        return $contents;
    }
}
