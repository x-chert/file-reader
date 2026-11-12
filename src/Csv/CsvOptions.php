<?php

declare(strict_types=1);

namespace Xchert\FileReader\Csv;

use Xchert\Util\Pod\Pod;
use Xchert\Util\Trait\FlagTrait;

class CsvOptions extends Pod
{
    use FlagTrait;

    public const string DEFAULT_DELIMITER = ';';
    public const string DEFAULT_ENCLOSURE = '"';
    public const string DEFAULT_ESCAPE = '';

    public function __construct(
        protected string $delimiter = self::DEFAULT_DELIMITER,
        protected string $enclosure = self::DEFAULT_ENCLOSURE,
        protected string $escape = self::DEFAULT_ESCAPE,
        protected HeaderBehavior $headerBehavior = HeaderBehavior::FlatHeader,
        string ...$flags
    ) {
        // fgetcsv only supports single byte characters
        if (\strlen($delimiter) !== 1) {
            throw new \InvalidArgumentException(\sprintf('Delimiter must be a single byte character. "%s" given', $delimiter));
        }

        if (\strlen($enclosure) !== 1) {
            throw new \InvalidArgumentException(\sprintf('Enclosure must be a single byte character. "%s" given', $enclosure));
        }

        if (\strlen($escape) > 1) {
            throw new \InvalidArgumentException(\sprintf('Escape must be empty or a single byte character. "%s" given', $escape));
        }

        $this->setFlags(...$flags);
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    public function getEscape(): string
    {
        return $this->escape;
    }

    public function getHeaderBehavior(): HeaderBehavior
    {
        return $this->headerBehavior;
    }
}
