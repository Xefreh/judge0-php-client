<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Utils;

use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class ArchiveBuilder
{
    /**
     * Creates an archive from file contents (strings)
     *
     * @param array<string, string> $files Associative array [relative/path => content]
     * @param string $runScript Bash script for execution
     * @param string|null $compileScript Bash script for compilation (optional)
     * @return string Base64-encoded zip archive
     * @throws InvalidArgumentException If parameters are invalid
     * @throws RuntimeException If archive creation fails
     */
    public static function createArchive(
        array $files,
        string $runScript,
        ?string $compileScript = null
    ): string {
        self::validateInputs($files, $runScript);

        return self::buildArchive($files, $runScript, $compileScript);
    }

    /**
     * Creates an archive from file paths on disk
     *
     * @param array<string, string> $files Associative array [relative/path/in/archive => absolute/path/on/disk]
     * @param string $runScript Bash script for execution
     * @param string|null $compileScript Bash script for compilation (optional)
     * @return string Base64-encoded zip archive
     * @throws InvalidArgumentException If parameters are invalid or a file does not exist
     * @throws RuntimeException If archive creation fails
     */
    public static function createArchiveFromFiles(
        array $files,
        string $runScript,
        ?string $compileScript = null
    ): string {
        self::validateInputs($files, $runScript);

        $contents = [];
        foreach ($files as $archivePath => $diskPath) {
            if (!file_exists($diskPath)) {
                throw new InvalidArgumentException("File not found: {$diskPath}");
            }

            if (!is_readable($diskPath)) {
                throw new InvalidArgumentException("File not readable: {$diskPath}");
            }

            $content = file_get_contents($diskPath);
            if ($content === false) {
                throw new RuntimeException("Failed to read file: {$diskPath}");
            }

            $contents[$archivePath] = $content;
        }

        return self::buildArchive($contents, $runScript, $compileScript);
    }

    /**
     * Validates input parameters
     *
     * @param array<string, string> $files
     * @param string $runScript
     * @throws InvalidArgumentException
     */
    private static function validateInputs(array $files, string $runScript): void
    {
        if (empty($files)) {
            throw new InvalidArgumentException('Files array cannot be empty');
        }

        if (trim($runScript) === '') {
            throw new InvalidArgumentException('Run script cannot be empty');
        }
    }

    /**
     * Builds the zip archive and returns its base64-encoded content
     *
     * @param array<string, string> $files Associative array [path => content]
     * @param string $runScript
     * @param string|null $compileScript
     * @return string
     * @throws RuntimeException
     */
    private static function buildArchive(
        array $files,
        string $runScript,
        ?string $compileScript
    ): string {
        $tempFile = tempnam(sys_get_temp_dir(), 'judge0_archive_');
        if ($tempFile === false) {
            throw new RuntimeException('Failed to create temporary file');
        }

        try {
            $zip = new ZipArchive();
            $result = $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== true) {
                throw new RuntimeException("Failed to create zip archive: error code {$result}");
            }

            $zip->addFromString('run', $runScript);

            if ($compileScript !== null) {
                $zip->addFromString('compile', $compileScript);
            }

            foreach ($files as $path => $content) {
                $zip->addFromString($path, $content);
            }

            $zip->close();

            $archiveContent = file_get_contents($tempFile);
            if ($archiveContent === false) {
                throw new RuntimeException('Failed to read archive content');
            }

            return base64_encode($archiveContent);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
