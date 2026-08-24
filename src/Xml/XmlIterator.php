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

        $hasEncoding = $this->hasEncoding($stream);
        \rewind($stream);

        if (!$hasEncoding) {
            IoUtil::appendCharacterSetFilter($stream, $this->charsetOptions);
        }

        $uri = \stream_get_meta_data($stream)['uri'] ?? null;

        if ($uri) {
            $reader = \XMLReader::open($uri);
        } else {
            $reader = \XMLReader::fromStream($stream);
        }

        yield from $this->readXml($reader, $offset, $limit);

        $reader->close();
    }

    protected function readXml(\XMLReader $reader, int $offset = 0, ?int $limit = null): \Generator
    {
        $currentPath = [];
        $iteration = 0;
        $fetched = 0;

        while ($reader->read()) {
            if ($reader->nodeType === \XMLReader::ELEMENT) {
                $currentPath[] = $reader->localName;

                if ($this->isInsideTarget($currentPath)) {
                    if ($iteration >= $offset) {
                        $outerXml = $reader->readOuterXml();

                        if (!empty($outerXml)) {
                            $xmlElement = \simplexml_load_string($outerXml);

                            yield \json_decode(\json_encode($xmlElement), true);
                            $fetched++;

                            if ($fetched === $limit) {
                                break;
                            }
                        }
                    }

                    $iteration++;
                    \array_pop($currentPath);
                    $reader->next();

                    if ($reader->nodeType === \XMLReader::NONE) {
                        break;
                    }

                    if ($reader->nodeType === \XMLReader::ELEMENT) {
                        $currentPath[] = $reader->localName;
                    }
                }
            }

            if ($reader->nodeType === \XMLReader::END_ELEMENT) {
                \array_pop($currentPath);
            }
        }
    }

    private function isInsideTarget(array $currentPath): bool
    {
        $targetLength = \count($this->path);
        $currentLength = \count($currentPath);

        if ($currentLength !== $targetLength + 1) {
            return false;
        }

        for ($i = 0; $i < $targetLength; $i++) {
            if ($currentPath[$i] !== $this->path[$i]) {
                return false;
            }
        }

        return true;
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

        $header = \str_replace('\x00', '', $header);

        return (bool)\preg_match('/^<\?xml\s+[^>]*encoding=/i', $header);
    }
}