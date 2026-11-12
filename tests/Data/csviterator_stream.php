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
            'data' => fopen(__DIR__.'/products_utf8.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_utf8.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_utf8.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_utf8.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_iso88591.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_utf8_bom.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_utf8.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_utf8.csv', 'r'),
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
            'data' => fopen(__DIR__.'/products_utf8.csv', 'r'),
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
    ];
})();