<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xchert\FileReader\Xml\XmlOptions;

final class XmlOptionsTest extends TestCase
{
    public static function invalidNamespaces(): iterable
    {
        yield 'NumericPrefix' => [['http://example.com/a']];
        yield 'EmptyPrefix' => [['' => 'http://example.com/a']];
        yield 'PrefixWithColon' => [['a:b' => 'http://example.com/a']];
        yield 'EmptyUri' => [['a' => '']];
        yield 'NonStringUri' => [['a' => 1]];
        yield 'DuplicateUri' => [['a' => 'http://example.com/a', 'b' => 'http://example.com/a']];
    }

    #[DataProvider('invalidNamespaces')]
    public function testInvalidNamespaces(array $namespaces): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new XmlOptions($namespaces);
    }

    public function testNamespaces(): void
    {
        $options = new XmlOptions(['g' => 'http://base.google.com/ns/1.0']);

        $this->assertSame(['g' => 'http://base.google.com/ns/1.0'], $options->getNamespaces());
        $this->assertSame('http://base.google.com/ns/1.0', $options->getNamespaceUri('g'));
        $this->assertNull($options->getNamespaceUri('atom'));
        $this->assertSame('g', $options->getPrefix('http://base.google.com/ns/1.0'));
        $this->assertNull($options->getPrefix('http://www.w3.org/2005/Atom'));
    }
}
