<?php

declare(strict_types=1);

namespace Xchert\FileReader\Exception;

class InvalidCharsetException extends \Exception
{
    public function __construct(private readonly string $charset)
    {
        parent::__construct(\sprintf('%s is not a valid charset', $charset));
    }

    public function getCharset(): string
    {
        return $this->charset;
    }
}