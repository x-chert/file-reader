<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Test\Data\FileDataProvider;
use Xchert\FileReader\Xml\XmlIterator;

class XmlIteratorTest extends TestCase
{
    #[DataProviderExternal(FileDataProvider::class, 'xmliterator_file')]
    public function testIterateFile(
        array $path,
        CharsetOptions $charsetOptions,
        string $file,
        mixed $expected,
        ?int $offset = null,
        ?int $limit = null,
        ?string $expectedException = null
    ): void {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $iterator = new XmlIterator($path, $charsetOptions);
        $result = [];

        foreach ($iterator->iterateFile($file, $offset, $limit) as $row) {
            $result[] = $row;
        }

        if ($expectedException === null) {
            $this->assertEquals($expected, $result);
        }
    }

    #[DataProviderExternal(FileDataProvider::class, 'xmliterator_string')]
    public function testIterateString(
        array $path,
        CharsetOptions $charsetOptions,
        string $data,
        mixed $expected,
        ?int $offset = null,
        ?int $limit = null,
        ?string $expectedException = null
    ): void {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $iterator = new XmlIterator($path, $charsetOptions);
        $result = [];

        foreach ($iterator->iterateString($data, $offset, $limit) as $row) {
            $result[] = $row;
        }

        if ($expectedException === null) {
            $this->assertEquals($expected, $result);
        }
    }

    #[DataProviderExternal(FileDataProvider::class, 'xmliterator_stream')]
    public function testIterateStream(
        array $path,
        CharsetOptions $charsetOptions,
        $data,
        mixed $expected,
        ?int $offset = null,
        ?int $limit = null,
        ?string $expectedException = null
    ): void {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $iterator = new XmlIterator($path, $charsetOptions);
        $result = [];

        try {
            foreach ($iterator->iterateStream($data, $offset, $limit) as $row) {
                $result[] = $row;
            }
        } finally {
            \fclose($data);
        }

        if ($expectedException === null) {
            $this->assertEquals($expected, $result);
        }
    }
}