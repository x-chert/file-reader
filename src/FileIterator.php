<?php

declare(strict_types=1);

namespace Xchert\FileReader;

use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Middleware\MiddlewareInterface;

abstract class FileIterator
{
    /** @param MiddlewareInterface[] $middlewares */
    public function __construct(
        protected readonly CharsetOptions $charsetOptions = new CharsetOptions(),
        protected readonly array $middlewares = []
    ) {
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new \InvalidArgumentException(\sprintf('Middleware must implement %s. %s given', MiddlewareInterface::class, \get_debug_type($middleware)));
            }
        }
    }

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

    /**
     * Passes the record through all middlewares in the given order.
     */
    protected function applyMiddlewares(mixed $record): mixed
    {
        foreach ($this->middlewares as $middleware) {
            $record = $middleware->process($record);
        }

        return $record;
    }
}
