<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test\Data;

class FileDataProvider
{
    public static function csviterator_file(): iterable
    {
        return static::readFile(__DIR__.'/csviterator_file.php');
    }

    public static function readFile(string $file): iterable
    {
        if (!\file_exists($file) || !\is_readable($file)) {
            throw new \RuntimeException(\sprintf('File %s does not exist or is not readable.', $file));
        }

        return require $file;
    }
}