<?php

declare(strict_types=1);

namespace Xchert\FileReader\Exception;

class ReadError extends \Exception
{
    public function __construct(private readonly string $resourceName)
    {
        parent::__construct(\sprintf('%s is not readable', $resourceName));
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }
}
