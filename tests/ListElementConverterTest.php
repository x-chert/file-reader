<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xchert\FileReader\Xml\Middleware\ListElementConverter;
use Xchert\FileReader\Xml\XmlIterator;
use Xchert\Schema\Exception\SchemaNotFoundException;
use Xchert\Schema\SchemaRegistry;

final class ListElementConverterTest extends TestCase
{
    public function testSingleXmlElementsBecomeLists(): void
    {
        $iterator = new XmlIterator(['inventory'], middlewares: [new ListElementConverter($this->registry(), 'product')]);

        $result = \iterator_to_array($iterator->iterateString(<<<'XML'
            <inventory>
                <product id="1"><tag>new</tag><variant><sku>A</sku></variant><categories><category>56</category></categories></product>
                <product id="2"><tag>new</tag><tag>sale</tag><variant><sku>B</sku></variant><variant><sku>C</sku></variant><categories/></product>
            </inventory>
            XML), false);

        $this->assertSame([
            ['@id' => '1', 'tag' => ['new'], 'variant' => [['sku' => 'A']], 'categories' => ['56']],
            ['@id' => '2', 'tag' => ['new', 'sale'], 'variant' => [['sku' => 'B'], ['sku' => 'C']], 'categories' => []],
        ], $result);
    }

    #[DataProvider('provideRecords')]
    public function testProcess(mixed $record, mixed $expected): void
    {
        $this->assertSame($expected, new ListElementConverter($this->registry(), 'product')->process($record));
    }

    public static function provideRecords(): iterable
    {
        yield 'single element' => [['tag' => 'new'], ['tag' => ['new']]];
        yield 'list stays list' => [['tag' => ['new', 'sale']], ['tag' => ['new', 'sale']]];
        yield 'empty element' => [['tag' => ''], ['tag' => []]];
        yield 'wrapper with single child' => [['categories' => ['category' => '56']], ['categories' => ['56']]];
        yield 'wrapper with multiple children' => [['categories' => ['category' => ['56', '65']]], ['categories' => ['56', '65']]];
        yield 'wrapper of objects' => [['variant' => ['variants' => ['sku' => 'A']]], ['variant' => [['sku' => 'A']]]];
        yield 'single object with one property is no wrapper' => [['variant' => ['sku' => 'A']], ['variant' => [['sku' => 'A']]]];
        yield 'single element with attribute is no wrapper' => [['tag' => ['@lang' => 'de']], ['tag' => [['@lang' => 'de']]]];
        yield 'element with attributes and text' => [['tag' => ['@lang' => 'de', '#' => 'neu']], ['tag' => [['@lang' => 'de', '#' => 'neu']]]];
        yield 'list in nested object' => [['detail' => ['color' => 'red']], ['detail' => ['color' => ['red']]]];
        yield 'list in list item' => [['variant' => ['sku' => 'A', 'size' => 'XL']], ['variant' => [['sku' => 'A', 'size' => ['XL']]]]];
        yield 'other values are unchanged' => [['id' => '1', 'unknown' => ['a' => 'b'], 'detail' => ''], ['id' => '1', 'unknown' => ['a' => 'b'], 'detail' => '']];
        yield 'missing list is not added' => [[], []];
        yield 'text element' => ['text', 'text'];
    }

    public function testMissingNestedSchemaKeepsElementUnchanged(): void
    {
        $registry = SchemaRegistry::fromArray([
            [
                'name' => 'product',
                'properties' => [
                    ['name' => 'tag', 'type' => 'list<string>'],
                    ['name' => 'detail', 'type' => 'object<missing>'],
                    ['name' => 'variant', 'type' => 'list<object<missing>>'],
                ],
            ],
        ]);

        $record = [
            'tag' => 'new',
            'detail' => ['color' => 'red'],
            'variant' => ['sku' => 'A'],
        ];

        $this->assertSame(
            ['tag' => ['new']] + $record,
            new ListElementConverter($registry, 'product')->process($record)
        );
    }

    public function testUnknownSchema(): void
    {
        $this->expectException(SchemaNotFoundException::class);

        new ListElementConverter($this->registry(), 'unknown');
    }

    private function registry(): SchemaRegistry
    {
        return SchemaRegistry::fromArray([
            [
                'name' => 'product',
                'properties' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'tag', 'type' => 'list<string>'],
                    ['name' => 'categories', 'type' => 'list<string>'],
                    ['name' => 'variant', 'type' => 'list<object<variant>>'],
                    ['name' => 'detail', 'type' => 'object<detail>'],
                ],
            ],
            [
                'name' => 'variant',
                'properties' => [
                    ['name' => 'sku', 'type' => 'string'],
                    ['name' => 'size', 'type' => 'list<string>'],
                ],
            ],
            ['name' => 'detail', 'properties' => [['name' => 'color', 'type' => 'list<string>']]],
        ]);
    }
}
