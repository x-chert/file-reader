<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;
use Xchert\FileReader\Test\Data\FileDataProvider;

final class CsvIteratorTest extends TestCase
{
    #[DataProviderExternal(FileDataProvider::class, 'csviterator_file')]
    public function testIterateFile(
        CsvOptions $csvOptions,
        CharsetOptions $charsetOptions,
        string $file,
        mixed $expected,
        ?int $offset = null,
        ?int $limit = null,
        ?string $expectedException = null
    ): void {
        $iterator = new CsvIterator($csvOptions, $charsetOptions);
        $result = [];

        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        foreach ($iterator->iterateFile($file, $offset, $limit) as $row) {
            $result[] = $row;
        }

        if ($expectedException === null) {
            $this->assertEquals($expected, $result);
        }
    }
}