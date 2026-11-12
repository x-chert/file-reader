<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test;

use PHPUnit\Framework\TestCase;
use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;
use Xchert\FileReader\Csv\HeaderBehavior;
use Xchert\FileReader\Middleware\Middleware;
use Xchert\FileReader\Xml\XmlIterator;

final class MiddlewareTest extends TestCase
{
    public function testCsvRecordsPassMiddlewaresInOrder(): void
    {
        $iterator = new CsvIterator(new CsvOptions(), middlewares: [
            new Middleware(static fn(array $record) => $record + ['first' => 'a']),
            new Middleware(static fn(array $record) => $record + ['second' => $record['first'].'b']),
        ]);

        $result = \iterator_to_array($iterator->iterateString("id;name\n1;Mouse\n2;Keyboard"), false);

        $this->assertSame([
            ['id' => '1', 'name' => 'Mouse', 'first' => 'a', 'second' => 'ab'],
            ['id' => '2', 'name' => 'Keyboard', 'first' => 'a', 'second' => 'ab'],
        ], $result);
    }

    public function testCsvRecordsWithoutHeaderPassMiddlewares(): void
    {
        $iterator = new CsvIterator(new CsvOptions(headerBehavior: HeaderBehavior::NoHeader), middlewares: [
            new Middleware(static fn(array $record) => \array_map(\strtoupper(...), $record)),
        ]);

        $result = \iterator_to_array($iterator->iterateString("a;b\nc;d"), false);

        $this->assertSame([['A', 'B'], ['C', 'D']], $result);
    }

    public function testXmlRecordsPassMiddlewares(): void
    {
        $iterator = new XmlIterator(['inventory'], middlewares: [
            new Middleware(static fn(array $record) => $record['name']),
        ]);

        $result = \iterator_to_array(
            $iterator->iterateString(
                '<inventory><product><name>Mouse</name></product><product><name>Keyboard</name></product></inventory>'
            ),
            false
        );

        $this->assertSame(['Mouse', 'Keyboard'], $result);
    }

    public function testMiddlewaresOnlyProcessRecordsWithinOffsetAndLimit(): void
    {
        $processed = [];
        $iterator = new CsvIterator(new CsvOptions(), middlewares: [
            new Middleware(static function (array $record) use (&$processed) {
                $processed[] = $record['id'];

                return $record;
            }),
        ]);

        \iterator_to_array($iterator->iterateString("id\n1\n2\n3\n4", 1, 2), false);

        $this->assertSame(['2', '3'], $processed);
    }

    public function testInvalidMiddleware(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvIterator(new CsvOptions(), middlewares: [new \stdClass()]);
    }

    public function testMiddlewareAcceptsAnyCallable(): void
    {
        $this->assertSame('ABC', new Middleware('strtoupper')->process('abc'));
        $this->assertSame(['b'], new Middleware(\array_values(...))->process([1 => 'b']));
    }
}
