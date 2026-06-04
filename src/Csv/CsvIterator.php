<?php

namespace Xchert\FileReader\Csv;

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Exception\ReadError;
use Xchert\FileReader\FileIterator;
use Xchert\FileReader\IoUtil;
use Xchert\Util\Value;

class CsvIterator extends FileIterator
{
    public const string SKIP_EMPTY = 'skip_empty';

    public function __construct(
        protected CsvOptions $csvOptions,
        CharsetOptions $charsetOptions = new CharsetOptions()
    ) {
        parent::__construct($charsetOptions);
    }

    public function iterateFile(string $file, ?int $offset = null, ?int $limit = null): \Generator
    {
        if(!$this->isFileReadable($file)) {
            throw new ReadError($file);
        }

        $stream = @\fopen($file, 'r');

        if($stream === false) {
            throw new ReadError($file);
        }

        yield from $this->iterateStream($stream, $offset, $limit);

        \fclose($stream);
    }

    public function iterateString(string $content, ?int $offset = null, ?int $limit = null): \Generator
    {
        $stream = @\fopen('php://memory', 'w+');
        \fwrite($stream, $content);
        \rewind($stream);

        yield from $this->iterateStream($stream, $offset, $limit);

        \fclose($stream);
    }

    /** @var resource $stream */
    public function iterateStream($stream, ?int $offset = null, ?int $limit = null): \Generator
    {
        if(!\is_resource($stream)) {
            throw new \InvalidArgumentException(\sprintf('Stream must be a resource. %s given', \get_debug_type($stream)));
        }

        if($limit !== null && $limit <= 0) {
            throw new \InvalidArgumentException('Limit must be greater than 0');
        }

        if($offset !== null && $offset < 0) {
            throw new \InvalidArgumentException('Offset must be greater than or equal to 0');
        }

        IoUtil::appendCharacterSetFilter($stream, $this->charsetOptions);

        $header = null;
        $fetched = 0;
        $iteration = 0;
        $headerBehavior = $this->csvOptions->getHeaderBehavior();

        if($headerBehavior->hasHeader()) {
            // Read first line of file for header
            $header = $this->readCsv($stream);

            // Stream is empty
            if($header === null || $this->isEmpty($header)) {
                return;
            }

            // Ignore header if it's not in use
            if(!$headerBehavior->isUsingHeader()) {
                $header = null;
            }
        }

        while(($record = $this->readCsv($stream)) !== null) {
            if($this->csvOptions->hasFlags(self::SKIP_EMPTY) && $this->isEmpty($record)) {
                continue;
            }

            if($offset !== null && $iteration < $offset) {
                $iteration++;
                continue;
            }

            $iteration++;

            if(!$headerBehavior->isUsingHeader()) {
                $fetched++;
                yield $record;

                if($fetched === $limit) {
                    return;
                }

                continue;
            }

            \assert(\is_array($header));
            yield $this->buildRecord($header, $record);
            $fetched++;

            if($fetched === $limit) {
                return;
            }
        }
    }

    /**
     * @param resource $stream
     */
    protected function readCsv($stream): ?array
    {
        if(!\is_resource($stream)) {
            return null;
        }

        return \fgetcsv($stream, null, $this->csvOptions->getDelimiter(), $this->csvOptions->getEnclosure(), $this->csvOptions->getEscape()) ?: null;
    }

    protected function isEmpty(array $data): bool
    {
        foreach($data as $value) {
            if(!Value::isEmpty($value, false)) {
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

        if($headerCount > $dataCount) {
            $data = static::append($data, $headerCount - $dataCount, '');
        } elseif($dataCount > $headerCount) {
            $data = \array_slice($data, 0, $headerCount);
        }

        return \array_combine($header, $data);
    }

    protected function buildNested(array $header, array $data): array
    {
        $stream = \fopen('php://memory', 'w+');
        \fputcsv($stream, $header, ';', '"', '');
        \fputcsv($stream, $data, ';', '"', '');
        \rewind($stream);

        $content = \stream_get_contents($stream);
        \fclose($stream);

        $decoder = new CsvEncoder([
            CsvEncoder::DELIMITER_KEY => ';',
            CsvEncoder::ENCLOSURE_KEY => '"',
            CsvEncoder::AS_COLLECTION_KEY => false
        ]);

        $result = $decoder->decode($content, CsvEncoder::FORMAT);

        if(!\is_array($result)) {
            throw new \RuntimeException('Data could not be re-decoded correctly.');
        }

        return $result;
    }

    protected function append(array $array, int $count, mixed $value): array
    {
        for($i = 1; $i <= $count; $i++) {
            $array[] = $value;
        }

        return $array;
    }
}
