<?php

namespace Xchert\FileReader\Exception;

use Xchert\Exception\ErrorException;

class ReadError extends ErrorException
{
    public const string ERROR_CODE = 'XCHERT_FILE_READER__READ_ERROR';

    public function __construct(string $resourceName, ?string $reason = null)
    {
        $params = [
            'resourceName' => $resourceName,
        ];

        if($reason !== null) {
            $params['reason'] = $reason;
        }

        parent::__construct(
            'Resource {{ resourceName }} is not readable.',
            $params
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
