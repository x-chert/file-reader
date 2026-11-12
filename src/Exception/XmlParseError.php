<?php

declare(strict_types=1);

namespace Xchert\FileReader\Exception;

class XmlParseError extends \Exception
{
    public function __construct(private readonly \LibXMLError $error)
    {
        parent::__construct(\sprintf(
            'XML parse error on line %d, column %d: %s',
            $error->line,
            $error->column,
            \trim($error->message),
        ));
    }

    public function getError(): \LibXMLError
    {
        return $this->error;
    }
}
