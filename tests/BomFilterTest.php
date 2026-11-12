<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;
use Xchert\FileReader\Test\Data\ChunkedStream;

final class BomFilterTest extends TestCase
{
    public static function contents(): iterable
    {
        yield 'LeadingBom' => ["\xef\xbb\xbfabc", 'abc'];
        yield 'NoBom' => ['abc', 'abc'];
        yield 'BomInsideData' => ["a\xef\xbb\xbfb", "a\xef\xbb\xbfb"];
        yield 'OnlyFirstBomRemoved' => ["\xef\xbb\xbf\xef\xbb\xbfabc", "\xef\xbb\xbfabc"];
        yield 'OnlyBom' => ["\xef\xbb\xbf", ''];
        yield 'IncompleteBom' => ["\xef\xbb", "\xef\xbb"];
        yield 'BomPrefix' => ["\xefabc", "\xefabc"];
        yield 'Empty' => ['', ''];
    }

    #[DataProvider('contents')]
    public function testFilter(string $content, string $expected): void
    {
        $stream = \fopen('php://memory', 'w+');
        \fwrite($stream, $content);
        \rewind($stream);
        \stream_filter_append($stream, 'bom_filter', \STREAM_FILTER_READ);

        $this->assertSame($expected, \stream_get_contents($stream));
    }

    #[DataProvider('contents')]
    public function testFilterWithSingleByteBuckets(string $content, string $expected): void
    {
        $stream = ChunkedStream::open($content);
        \stream_filter_append($stream, 'bom_filter', \STREAM_FILTER_READ);

        $this->assertSame($expected, \stream_get_contents($stream));
    }

    public function testCsvValueKeepsBom(): void
    {
        $iterator = new CsvIterator(new CsvOptions());

        $this->assertSame(
            [['id' => '1', 'name' => "a\u{FEFF}b"]],
            \iterator_to_array($iterator->iterateString("\u{FEFF}id;name\n1;a\u{FEFF}b"), false)
        );
    }
}
