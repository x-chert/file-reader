<?php

declare(strict_types=1);

namespace Xchert\FileReader\Csv;

use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Exception\CharsetConversionException;
use Xchert\FileReader\Exception\ReadError;
use Xchert\FileReader\FileIterator;
use Xchert\FileReader\Io\IoUtil;
use Xchert\FileReader\Middleware\MiddlewareInterface;
use Xchert\Util\Value;

class CsvIterator extends FileIterator
{
    public const string SKIP_EMPTY = 'skip_empty';

    /** @param MiddlewareInterface[] $middlewares */
    public function __construct(
        protected readonly CsvOptions $csvOptions,
        CharsetOptions $charsetOptions = new CharsetOptions(),
        array $middlewares = []
    ) {
        parent::__construct($charsetOptions, $middlewares);
    }

    public function iterateFile(string $file, ?int $offset = null, ?int $limit = null): \Generator
    {
        if (!$this->isFileReadable($file)) {
            throw new ReadError($file);
        }

        $stream = @\fopen($file, 'r');

        if ($stream === false) {
            throw new ReadError($file);
        }

        try {
            yield from $this->iterateStream($stream, $offset, $limit);
        } finally {
            \fclose($stream);
        }
    }

    public function iterateString(string $content, ?int $offset = null, ?int $limit = null): \Generator
    {
        $stream = @\fopen('php://temp', 'w+');
        \fwrite($stream, $content);
        \rewind($stream);

        try {
            yield from $this->iterateStream($stream, $offset, $limit);
        } finally {
            \fclose($stream);
        }
    }

    /** @param resource $stream */
    public function iterateStream($stream, ?int $offset = null, ?int $limit = null): \Generator
    {
        if (!\is_resource($stream)) {
            throw new \InvalidArgumentException(\sprintf('Stream must be a resource. %s given', \get_debug_type($stream)));
        }

        $this->validateOffsetLimit($offset, $limit);
        IoUtil::appendCharacterSetFilter($stream, $this->charsetOptions);

        $header = null;
        $fetched = 0;
        $skipped = 0;
        $headerBehavior = $this->csvOptions->getHeaderBehavior();
        $skipEmpty = $this->csvOptions->hasFlags(self::SKIP_EMPTY);

        if ($headerBehavior->hasHeader()) {
            // Read first non-empty line of file for header
            do {
                $header = $this->readCsv($stream);
            } while ($header !== null && $this->isEmpty($header));

            // Stream is empty
            if ($header === null) {
                return;
            }

            // Ignore header if it's not in use
            if (!$headerBehavior->isUsingHeader()) {
                $header = null;
            }
        }

        while (($record = $this->readCsv($stream)) !== null) {
            // fgetcsv returns [null] for blank lines, which are no records
            if ($record === [null] || ($skipEmpty && $this->isEmpty($record))) {
                continue;
            }

            if ($offset !== null && $skipped < $offset) {
                $skipped++;
                continue;
            }

            yield $this->applyMiddlewares($header === null ? $record : $this->buildRecord($header, $record));

            if (++$fetched === $limit) {
                return;
            }
        }
    }

    /**
     * @param resource $stream
     *
     * @throws CharsetConversionException
     */
    protected function readCsv($stream): ?array
    {
        if (!\is_resource($stream)) {
            return null;
        }

        \set_error_handler(
            function (int $severity, string $message) {
                if ($severity === \E_WARNING && \str_contains($message, 'iconv')) {
                    throw new CharsetConversionException($message);
                }

                return false;
            }
        );

        try {
            $data = \fgetcsv($stream, null, $this->csvOptions->getDelimiter(), $this->csvOptions->getEnclosure(), $this->csvOptions->getEscape()) ?: null;
        } finally {
            \restore_error_handler();
        }

        return $data;
    }

    protected function isEmpty(array $data): bool
    {
        foreach ($data as $value) {
            if (!Value::isEmpty($value, false)) {
                return false;
            }
        }

        return true;
    }

    protected function buildRecord(array $header, array $data): array
    {
        return match ($this->csvOptions->getHeaderBehavior()) {
            HeaderBehavior::FlatHeader => $this->buildFlat($header, $data),
            HeaderBehavior::NestedHeader => $this->buildNested($header, $data),
            HeaderBehavior::NoHeader, HeaderBehavior::SkipHeader => $data
        };
    }

    protected function buildFlat(array $header, array $data): array
    {
        $headerCount = \count($header);
        $dataCount = \count($data);

        if ($headerCount > $dataCount) {
            $data = \array_pad($data, $headerCount, '');
        } elseif ($dataCount > $headerCount) {
            $data = \array_slice($data, 0, $headerCount);
        }

        return \array_combine($header, $data);
    }

    /**
     * Builds nested arrays from dot-notation header keys, e.g. "price.amount"
     */
    protected function buildNested(array $header, array $data): array
    {
        $result = [];
        $count = \count($header);

        for ($i = 0; $i < $count; $i++) {
            $keys = \explode('.', (string)$header[$i]);
            $last = \array_key_last($keys);
            $target = &$result;

            foreach ($keys as $index => $key) {
                // Empty key segments are replaced by the column index
                $key = $key === '' ? $i : $key;

                if ($index === $last) {
                    $target[$key] = $data[$i] ?? '';
                    break;
                }

                $target[$key] ??= [];
                $target = &$target[$key];
            }

            unset($target);
        }

        return $result;
    }
}
