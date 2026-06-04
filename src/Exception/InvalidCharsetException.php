<?php

namespace Xchert\FileReader\Exception;

use Symfony\Component\HttpFoundation\Response;
use Xchert\Exception\ErrorException;

class InvalidCharsetException extends ErrorException
{
    public const string ERROR_CODE = 'XCHERT_FILE_READER__INVALID_CHARSET';

    public function __construct(string $charset, ?string $source = null)
    {
        parent::__construct(
            '{{ charset }} is not a valid charset.',
            ['charset' => $charset],
            $source ?? 'unknown'
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}