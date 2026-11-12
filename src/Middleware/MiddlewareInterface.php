<?php

declare(strict_types=1);

namespace Xchert\FileReader\Middleware;

interface MiddlewareInterface
{
    /**
     * Processes a record before it is returned by the iterator.
     * The returned value is passed to the next middleware or returned as record.
     */
    public function process(mixed $record): mixed;
}
