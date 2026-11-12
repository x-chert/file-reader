<?php

declare(strict_types=1);

use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Csv\CsvOptions;
use Xchert\FileReader\Csv\HeaderBehavior;

return (function (): Generator {
    yield from [
        'ReadFlatHeader' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8.csv'),
            'expected' => [
                [
                    'productNumber' => 'ABC-123',
                    'stock.WH-1' => '34',
                    'stock.WH-2' => '69',
                ],
                [
                    'productNumber' => 'DEF-456',
                    'stock.WH-1' => '12',
                    'stock.WH-2' => '23',
                ],
                [
                    'productNumber' => 'GHI-789',
                    'stock.WH-1' => '45',
                    'stock.WH-2' => '67',
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadNestedHeader' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::NestedHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8.csv'),
            'expected' => [
                [
                    'productNumber' => 'ABC-123',
                    'stock' => [
                        'WH-1' => '34',
                        'WH-2' => '69',
                    ]
                ],
                [
                    'productNumber' => 'DEF-456',
                    'stock' => [
                        'WH-1' => '12',
                        'WH-2' => '23',
                    ]
                ],
                [
                    'productNumber' => 'GHI-789',
                    'stock' => [
                        'WH-1' => '45',
                        'WH-2' => '67',
                    ]
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadNoHeader' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::NoHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8.csv'),
            'expected' => [
                [
                    'productNumber',
                    'stock.WH-1',
                    'stock.WH-2',
                ],
                [
                    'ABC-123',
                    '34',
                    '69',
                ],
                [
                    'DEF-456',
                    '12',
                    '23',
                ],
                [
                    'GHI-789',
                    '45',
                    '67'
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadSkipHeader' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::SkipHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8.csv'),
            'expected' => [
                [
                    'ABC-123',
                    '34',
                    '69',
                ],
                [
                    'DEF-456',
                    '12',
                    '23',
                ],
                [
                    'GHI-789',
                    '45',
                    '67'
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadISO-8859-1' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions('ISO-8859-1'),
            'data' => file_get_contents(__DIR__.'/products_iso88591.csv'),
            'expected' => [
                [
                    'productNumber' => 'ÄÖÜ-123',
                    'stock.WH-1' => '34',
                    'stock.WH-2' => '69',
                ],
                [
                    'productNumber' => 'ßüö-456',
                    'stock.WH-1' => '12',
                    'stock.WH-2' => '23',
                ],
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadUTF-8-BOM' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8_bom.csv'),
            'expected' => [
                [
                    'productNumber' => 'ABC-123',
                    'stock.WH-1' => '34',
                    'stock.WH-2' => '69',
                ],
                [
                    'productNumber' => 'DEF-456',
                    'stock.WH-1' => '12',
                    'stock.WH-2' => '23',
                ],
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadWithOffset' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8.csv'),
            'expected' => [
                [
                    'productNumber' => 'DEF-456',
                    'stock.WH-1' => '12',
                    'stock.WH-2' => '23',
                ],
                [
                    'productNumber' => 'GHI-789',
                    'stock.WH-1' => '45',
                    'stock.WH-2' => '67',
                ]
            ],
            'offset' => 1,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadWithLimit' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8.csv'),
            'expected' => [
                [
                    'productNumber' => 'ABC-123',
                    'stock.WH-1' => '34',
                    'stock.WH-2' => '69',
                ],
                [
                    'productNumber' => 'DEF-456',
                    'stock.WH-1' => '12',
                    'stock.WH-2' => '23',
                ]
            ],
            'offset' => null,
            'limit' => 2,
            'expectedException' => null
        ],
        'ReadWithOffsetLimit' => [
            'csvOptions' => new CsvOptions(
                delimiter: CsvOptions::DEFAULT_DELIMITER,
                enclosure: CsvOptions::DEFAULT_ENCLOSURE,
                escape: CsvOptions::DEFAULT_ESCAPE,
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => file_get_contents(__DIR__.'/products_utf8.csv'),
            'expected' => [
                [
                    'productNumber' => 'DEF-456',
                    'stock.WH-1' => '12',
                    'stock.WH-2' => '23',
                ]
            ],
            'offset' => 1,
            'limit' => 1,
            'expectedException' => null
        ],
        'SkipLeadingBlankLinesBeforeHeader' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "\n\nid;name\n1;Mouse",
            'expected' => [['id' => '1', 'name' => 'Mouse']],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'SkipBlankLines' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "id;name\n1;Mouse\n\n2;Keyboard\n\n",
            'expected' => [['id' => '1', 'name' => 'Mouse'], ['id' => '2', 'name' => 'Keyboard']],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'SkipBlankLinesWithoutHeader' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::NoHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "1;Mouse\n\n2;Keyboard",
            'expected' => [['1', 'Mouse'], ['2', 'Keyboard']],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'KeepRowsOfEmptyValues' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "id;name\n;\n1;Mouse",
            'expected' => [['id' => '', 'name' => ''], ['id' => '1', 'name' => 'Mouse']],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'BackslashIsNoEscapeCharacter' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::FlatHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "id;path\n1;\"C:\\dir\\\"\n2;\"say \"\"hi\"\"\"",
            'expected' => [['id' => '1', 'path' => 'C:\\dir\\'], ['id' => '2', 'path' => 'say "hi"']],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'NestedHeaderWithDeepAndEmptyKeys' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::NestedHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "id;price.amount;price.currency;meta..x;a.b.c\n1;9.99;EUR;y;z;extra",
            'expected' => [['id' => '1', 'price' => ['amount' => '9.99', 'currency' => 'EUR'], 'meta' => [3 => ['x' => 'y']], 'a' => ['b' => ['c' => 'z']]]],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'NestedHeaderWithMissingColumns' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::NestedHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "id;a.b;a.c\n1;x",
            'expected' => [['id' => '1', 'a' => ['b' => 'x']]],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'NestedHeaderWithSingleEmptyValue' => [
            'csvOptions' => new CsvOptions(
                headerBehavior: HeaderBehavior::NestedHeader,
            ),
            'charsetOptions' => new CharsetOptions(),
            'data' => "a.b\n\"\"",
            'expected' => [['a' => ['b' => '']]],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
    ];
})();