<?php

declare(strict_types=1);

namespace Xchert\FileReader\Middleware;

readonly class Middleware implements MiddlewareInterface
{
    private \Closure $callable;

    /** @param callable(mixed): mixed $callable */
    public function __construct(callable $callable)
    {
        $this->callable = $callable(...);
    }

    public function process(mixed $record): mixed
    {
        return ($this->callable)($record);
    }
}
