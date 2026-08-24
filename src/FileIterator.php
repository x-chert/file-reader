<?php

declare(strict_types=1);

namespace Xchert\FileReader;

use Xchert\FileReader\Charset\CharsetOptions;

abstract class FileIterator
{
    public function __construct(
        protected readonly CharsetOptions $charsetOptions = new CharsetOptions()
    ) {}

    abstract public function iterateFile(string $file, ?int $offset = null, ?int $limit = null): \Generator;

    abstract public function iterateString(string $content, ?int $offset = null, ?int $limit = null): \Generator;

    /** @param resource $stream */
    abstract public function iterateStream($stream, ?int $offset = null, ?int $limit = null): \Generator;

    protected function isFileReadable(string $file): bool
    {
        if (!\file_exists($file) || !\is_file($file)) {
            return false;
        }

        return \is_readable($file);
    }

    protected function validateOffsetLimit(?int $offset, ?int $limit): void
    {
        if ($limit !== null && $limit <= 0) {
            throw new \InvalidArgumentException('Limit must be greater than 0');
        }

        if ($offset !== null && $offset < 0) {
            throw new \InvalidArgumentException('Offset must be greater than or equal to 0');
        }
    }
}
