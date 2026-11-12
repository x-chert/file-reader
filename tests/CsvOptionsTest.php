<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xchert\FileReader\Csv\CsvOptions;

final class CsvOptionsTest extends TestCase
{
    public static function invalidOptions(): iterable
    {
        yield 'EmptyDelimiter' => ['', '"', ''];
        yield 'MultiCharacterDelimiter' => [';;', '"', ''];
        yield 'MultiByteDelimiter' => ['§', '"', ''];
        yield 'EmptyEnclosure' => [';', '', ''];
        yield 'MultiCharacterEnclosure' => [';', '""', ''];
        yield 'MultiCharacterEscape' => [';', '"', '\\\\'];
    }

    #[DataProvider('invalidOptions')]
    public function testInvalidOptions(string $delimiter, string $enclosure, string $escape): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvOptions($delimiter, $enclosure, $escape);
    }

    public static function validOptions(): iterable
    {
        yield 'Defaults' => [CsvOptions::DEFAULT_DELIMITER, CsvOptions::DEFAULT_ENCLOSURE, CsvOptions::DEFAULT_ESCAPE];
        yield 'Tab' => ["\t", "'", '\\'];
        yield 'EmptyEscape' => [',', '"', ''];
    }

    #[DataProvider('validOptions')]
    public function testValidOptions(string $delimiter, string $enclosure, string $escape): void
    {
        $options = new CsvOptions($delimiter, $enclosure, $escape);

        $this->assertSame([$delimiter, $enclosure, $escape], [$options->getDelimiter(), $options->getEnclosure(), $options->getEscape()]);
    }
}
