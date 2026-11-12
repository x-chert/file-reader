<?php

declare(strict_types=1);

namespace Xchert\FileReader\Xml;

use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Exception\InvalidCharsetException;
use Xchert\FileReader\Exception\ReadError;
use Xchert\FileReader\FileIterator;
use Xchert\FileReader\Io\IoUtil;
use Xchert\Util\Value;

class XmlIterator extends FileIterator
{
    public function __construct(
        protected readonly array $path,
        CharsetOptions $charsetOptions = new CharsetOptions(),
    ) {
        $this->validatePath($path);

        parent::__construct($charsetOptions);
    }

    /**
     * @throws InvalidCharsetException
     * @throws ReadError
     */
    public function iterateFile(string $file, ?int $offset = null, ?int $limit = null): \Generator
    {
        if (!$this->isFileReadable($file)) {
            throw new ReadError($file);
        }

        $stream = @\fopen($file, 'r');

        if ($stream === false) {
            throw new ReadError($file);
        }

        yield from $this->iterateStream($stream, $offset, $limit);

        \fclose($stream);
    }

    /**
     * @throws InvalidCharsetException
     */
    public function iterateString(string $content, ?int $offset = null, ?int $limit = null): \Generator
    {
        $stream = @\fopen('php://temp', 'w+');
        \fwrite($stream, $content);
        \rewind($stream);
        $hasEncoding = $this->hasEncoding($stream);
        \fclose($stream);

        if (!$hasEncoding) {
            $encoding = $this->charsetOptions->getEncoding();

            if (!IoUtil::isValidEncoding($encoding)) {
                throw new InvalidCharsetException($encoding);
            }

            $content = \iconv(
                $encoding,
                IoUtil::createIconvEncoding('UTF-8', ...$this->charsetOptions->getModifiers()),
                $content,
            );
        }

        $reader = \XMLReader::fromString($content);

        yield from $this->readXml($reader, $offset ?? 0, $limit);
    }

    /**
     * @throws InvalidCharsetException
     */
    public function iterateStream($stream, ?int $offset = null, ?int $limit = null): \Generator
    {
        if (!\is_resource($stream)) {
            throw new \InvalidArgumentException(\sprintf('Stream must be a resource. %s given', \get_debug_type($stream)));
        }

        $offset ??= 0;
        $this->validateOffsetLimit($offset, $limit);

        // Encoding detection rewinds the stream, so non-seekable streams are buffered first
        if (!\stream_get_meta_data($stream)['seekable']) {
            $buffer = \fopen('php://temp', 'w+');
            \stream_copy_to_stream($stream, $buffer);
            \rewind($buffer);

            yield from $this->iterateStream($buffer, $offset, $limit);

            \fclose($buffer);

            return;
        }

        $hasEncoding = $this->hasEncoding($stream);
        \rewind($stream);

        if (!$hasEncoding) {
            IoUtil::appendCharacterSetFilter($stream, $this->charsetOptions);
        }

        // Read from the stream itself (not its URI) so appended stream filters are applied
        $reader = \XMLReader::fromStream($stream);

        yield from $this->readXml($reader, $offset, $limit);

        $reader->close();
    }

    protected function readXml(\XMLReader $reader, int $offset = 0, ?int $limit = null): \Generator
    {
        $targetDepth = \count($this->path);
        $iteration = 0;
        $fetched = 0;
        $moved = $reader->read();

        while ($moved) {
            if ($reader->nodeType !== \XMLReader::ELEMENT) {
                $moved = $reader->read();
                continue;
            }

            $depth = $reader->depth;

            // Ancestor level: descend into a matching element, skip the subtree of a non-matching one
            if ($depth < $targetDepth) {
                $moved = $reader->localName === $this->path[$depth] ? $reader->read() : $reader->next();
                continue;
            }

            if ($iteration >= $offset) {
                $outerXml = $reader->readOuterXml();

                if (!empty($outerXml)) {
                    $xmlElement = \simplexml_load_string($outerXml);

                    yield \json_decode(\json_encode($xmlElement), true);
                    $fetched++;

                    if ($fetched === $limit) {
                        return;
                    }
                }
            }

            $iteration++;
            $moved = $reader->next();
        }
    }

    private function validatePath(array $path): void
    {
        foreach ($path as $segment) {
            if (!\is_string($segment) || Value::isEmpty($segment)) {
                throw new \InvalidArgumentException('Path must be an array of non-empty strings.');
            }
        }
    }

    private function hasEncoding($stream): bool
    {
        if (!\is_resource($stream)) {
            return false;
        }

        $maxHeaderBytes = 4096;
        \rewind($stream);

        $header = '';

        while (!\feof($stream) && \strlen($header) < $maxHeaderBytes) {
            $chunk = \fread($stream, 128);

            if (empty($chunk)) {
                break;
            }

            $header .= $chunk;

            if (\str_contains($header, '?>')) {
                break;
            }
        }

        \rewind($stream);

        // Remove NUL bytes (UTF-16) and a leading byte order mark (UTF-8, UTF-16 BE/LE)
        $header = \str_replace("\x00", '', $header);
        $header = \preg_replace('/^(\xEF\xBB\xBF|\xFE\xFF|\xFF\xFE)/', '', $header);

        return (bool)\preg_match('/^<\?xml\s[^>]*\bencoding\s*=/i', $header);
    }
}