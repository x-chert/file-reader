<?php

declare(strict_types=1);

namespace Xchert\FileReader\Xml;

use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Exception\InvalidCharsetException;
use Xchert\FileReader\Exception\ReadError;
use Xchert\FileReader\Exception\XmlParseError;
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
     * @throws XmlParseError
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
     * @throws XmlParseError
     */
    public function iterateString(string $content, ?int $offset = null, ?int $limit = null): \Generator
    {
        $stream = @\fopen('php://temp', 'w+');
        \fwrite($stream, $content);
        \rewind($stream);

        yield from $this->iterateStream($stream, $offset, $limit);

        \fclose($stream);
    }

    /**
     * @throws InvalidCharsetException
     * @throws XmlParseError
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
        $moved = $this->guard($reader->read(...));

        while ($moved) {
            if ($reader->nodeType !== \XMLReader::ELEMENT) {
                $moved = $this->guard($reader->read(...));
                continue;
            }

            $depth = $reader->depth;

            // Ancestor level: descend into a matching element, skip the subtree of a non-matching one
            if ($depth < $targetDepth) {
                $moved = $this->guard($reader->localName === $this->path[$depth] ? $reader->read(...) : $reader->next(...));
                continue;
            }

            if ($iteration >= $offset) {
                $outerXml = $this->guard($reader->readOuterXml(...));

                if (!empty($outerXml)) {
                    $xmlElement = $this->guard(fn() => \simplexml_load_string($outerXml));

                    yield \json_decode(\json_encode($xmlElement), true);
                    $fetched++;

                    if ($fetched === $limit) {
                        return;
                    }
                }
            }

            $iteration++;
            $moved = $this->guard($reader->next(...));
        }
    }

    /**
     * Runs a libxml operation and converts its errors into an exception instead of PHP warnings.
     * Internal error handling is only enabled for the operation itself, so it does not leak into
     * the caller's code between yields.
     *
     * @throws XmlParseError
     */
    private function guard(callable $operation): mixed
    {
        $useInternalErrors = \libxml_use_internal_errors(true);
        \libxml_clear_errors();

        try {
            $result = $operation();
            $errors = \libxml_get_errors();
        } finally {
            \libxml_clear_errors();
            \libxml_use_internal_errors($useInternalErrors);
        }

        foreach ($errors as $error) {
            if ($error->level >= \LIBXML_ERR_ERROR) {
                throw new XmlParseError($error);
            }
        }

        return $result;
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