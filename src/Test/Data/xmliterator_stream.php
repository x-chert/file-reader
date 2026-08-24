<?php

declare(strict_types=1);

use Xchert\FileReader\Charset\CharsetOptions;

return (function (): Generator {
    yield from [
        'NestedRead' => [
            'path' => ['inventory', 'products'],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/inventory_utf8.xml', 'r'),
            'expected' => [
                [
                    'id' => '101',
                    'name' => 'Gaming Mouse',
                    'price' => '49.99',
                    'categories' => [
                        'category' => [
                            '56',
                            '65'
                        ]
                    ]
                ],
                [
                    'id' => '102',
                    'name' => 'Mechanical keyboard',
                    'price' => '119.99',
                    'categories' => [
                        'category' => '56'
                    ]
                ],
                [
                    'id' => '103',
                    'name' => 'Monitor',
                    'price' => '219.99'
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'ReadSingleElement' => [
            'path' => ['inventory', 'products'],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/single_product_utf8.xml', 'r'),
            'expected' => [
                [
                    'id' => '101',
                    'name' => 'Gaming Mouse',
                    'price' => '49.99',
                    'categories' => [
                        'category' => [
                            '56',
                            '65'
                        ]
                    ]
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'EmptyPath' => [
            'path' => [],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/inventory_utf8.xml', 'r'),
            'expected' => [
                [
                    'products' => [
                        'product' => [
                            [
                                'id' => '101',
                                'name' => 'Gaming Mouse',
                                'price' => '49.99',
                                'categories' => [
                                    'category' => [
                                        '56',
                                        '65'
                                    ]
                                ]
                            ],
                            [
                                'id' => '102',
                                'name' => 'Mechanical keyboard',
                                'price' => '119.99',
                                'categories' => [
                                    'category' => '56'
                                ]
                            ],
                            [
                                'id' => '103',
                                'name' => 'Monitor',
                                'price' => '219.99'
                            ]
                        ]
                    ]
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'NestedReadISO88591' => [
            'path' => ['inventory', 'products'],
            'charsetOptions' => new CharsetOptions('ISO-8859-1'),
            'data' => fopen(__DIR__.'/inventory_iso88591.xml', 'r'),
            'expected' => [
                [
                    'id' => '101',
                    'name' => 'Gäming Maus',
                    'price' => '49.99',
                    'categories' => [
                        'category' => [
                            '56',
                            '65'
                        ]
                    ]
                ],
                [
                    'id' => '102',
                    'name' => 'Mechanische Tastatür',
                    'price' => '119.99',
                    'categories' => [
                        'category' => '56'
                    ]
                ],
                [
                    'id' => '103',
                    'name' => 'Monitor',
                    'price' => '219.99'
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'NestedReadUTF-8-BOM' => [
            'path' => ['inventory', 'products'],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/inventory_utf8bom.xml', 'r'),
            'expected' => [
                [
                    'id' => '101',
                    'name' => 'Gaming Mouse',
                    'price' => '49.99',
                    'categories' => [
                        'category' => [
                            '56',
                            '65'
                        ]
                    ]
                ],
                [
                    'id' => '102',
                    'name' => 'Mechanical keyboard',
                    'price' => '119.99',
                    'categories' => [
                        'category' => '56'
                    ]
                ],
                [
                    'id' => '103',
                    'name' => 'Monitor',
                    'price' => '219.99'
                ]
            ],
            'offset' => null,
            'limit' => null,
            'expectedException' => null
        ],
        'NestedReadWithOffset' => [
            'path' => ['inventory', 'products'],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/inventory_utf8.xml', 'r'),
            'expected' => [
                [
                    'id' => '102',
                    'name' => 'Mechanical keyboard',
                    'price' => '119.99',
                    'categories' => [
                        'category' => '56'
                    ]
                ],
                [
                    'id' => '103',
                    'name' => 'Monitor',
                    'price' => '219.99'
                ]
            ],
            'offset' => 1,
            'limit' => null,
            'expectedException' => null
        ],
        'NestedReadWithLimit' => [
            'path' => ['inventory', 'products'],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/inventory_utf8.xml', 'r'),
            'expected' => [
                [
                    'id' => '101',
                    'name' => 'Gaming Mouse',
                    'price' => '49.99',
                    'categories' => [
                        'category' => [
                            '56',
                            '65'
                        ]
                    ]
                ],
                [
                    'id' => '102',
                    'name' => 'Mechanical keyboard',
                    'price' => '119.99',
                    'categories' => [
                        'category' => '56'
                    ]
                ]
            ],
            'offset' => null,
            'limit' => 2,
            'expectedException' => null
        ],
        'NestedReadWithLimitAndOffset' => [
            'path' => ['inventory', 'products'],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/inventory_utf8.xml', 'r'),
            'expected' => [
                [
                    'id' => '102',
                    'name' => 'Mechanical keyboard',
                    'price' => '119.99',
                    'categories' => [
                        'category' => '56'
                    ]
                ]
            ],
            'offset' => 1,
            'limit' => 1,
            'expectedException' => null
        ],
        'InvalidArgumentException' => [
            'path' => ['', 123],
            'charsetOptions' => new CharsetOptions(),
            'data' => fopen(__DIR__.'/inventory_utf8.xml', 'r'),
            'expected' => null,
            'offset' => null,
            'limit' => null,
            'expectedException' => InvalidArgumentException::class
        ],
    ];
})();