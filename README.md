# xchert/file-reader

A memory-efficient and flexible PHP library for streaming and iterating files with built-in character set conversion

## Features

- **Memory Efficient:** Uses PHP Generators (`\Generator`) to iterate through large files without loading the entire
  file into memory.
- **Multiple Input Sources:** Process files, raw strings, or open stream resources seamlessly.
- **Charset Conversion:** Automatically handles character encoding transformations (e.g., ISO-8859-1 to UTF-8) during
  stream reading.
- **Pagination Support:** Built-in `offset` and `limit` support.
- **Middleware:** Process each record before it is returned.

## Installation

Install the package via Composer

```bash
composer require xchert/file-reader
```

## Basic Usage

### Read CSV data

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;
use Xchert\FileReader\Charset\CharsetOptions;

$iterator = new CsvIterator(
  csvOptions: new CsvOptions(
      delimiter: ';',                               // default
      enclosure: '"',                               // default
      escape: '',                                   // default; no escape character as in RFC 4180, quotes are escaped by doubling them
      headerBehavior: HeaderBehavior::FlatHeader,   // default
      flags: [CsvIterator::SKIP_EMPTY]              // optional; SKIP_EMPTY flag also skips rows with only empty values, e.g. ';;'
  ),
  charsetOptions: new CharsetOptions(               // optional; encoding of the csv data, defaults to UTF-8
      encoding: 'UTF-8'
  ),
  middlewares: []                                   // optional; see Middleware
);

// Offset and limit are always optional
$offset = 5;
$limit = 10;

// Iterate a file
foreach ($iterator->iterateFile('path/to/data.csv', $offset, $limit) as $row) {
    // Do something
}

// Iterate a string
foreach ($iterator->iterateString($someCsvData, $offset, $limit) as $row) {
    // Do something
}

// Iterate a stream resource
foreach ($iterator->iterateStream($aStreamResource, $offset, $limit) as $row) {
    // Do something
}
```

> The CsvIterator converts all data to UTF-8.

**Header behavior**

There are severeal options to handle csv headers:

- `HeaderBehavior::FlatHeader` *(default)*: Maps header row keys to each record row into a single-level associative
  array.
- `HeaderBehavior::NestedHeader`: Parses dot-notation header keys into nested associative arrays, e.g. `price.amount`
  becomes `['price' => ['amount' => …]]`.
- `HeaderBehavior::SkipHeader`: Skips the first header row and returns indexed arrays for subsequent rows.
- `HeaderBehavior::NoHeader`: Treats all lines, including the first line, as data rows.

Blank lines are always skipped. The header is read from the first non-empty line.

### Read XML data

The XmlIterator takes an array as path and reads all elements within this path.

```php
<?php

use Xchert\FileReader\Xml\XmlIterator;
use Xchert\FileReader\Xml\XmlOptions;
use Xchert\FileReader\Charset\CharsetOptions;


$iterator = new XmlIterator(
  path: ['inventory', 'products']                   // required; takes an array of strings, can also be empty to read from the root element
  charsetOptions: new CharsetOptions(               // optional; encoding of the csv data, defaults to UTF-8
      encoding: 'UTF-8'
  ),
  xmlOptions: new XmlOptions(                       // optional
      ['g' => 'http://base.google.com/ns/1.0'],     // optional; namespace prefixes used in path and output
      XmlOptions::STRIP_NAMESPACES                  // optional; flag to output names without namespace prefix
  ),
  middlewares: []                                   // optional; see Middleware
);

// Offset and limit are always optional
$offset = 5;
$limit = 10;

// Iterate a file
foreach ($iterator->iterateFile('path/to/data.xml', $offset, $limit) as $row) {
    // Do something
}

// Iterate a string
foreach ($iterator->iterateString($someXmlData, $offset, $limit) as $row) {
    // Do something
}

// Iterate a stream resource
foreach ($iterator->iterateStream($aStreamResource, $offset, $limit) as $row) {
    // Do something
}
```

> The XmlIterator converts all data to UTF-8.

**Output format**

Each element within the path is converted into an array, or into a string if it only contains text:

- Attributes are prefixed with `@`, their values are always strings.
- Child elements are keyed by their name, including a namespace prefix (e.g. `g:id`). Repeated elements become a list.
- Text and CDATA content is returned as string, or as `#` if the element also has attributes or child elements.
- Empty elements become an empty string. Comments and processing instructions are ignored.

```xml
<product id="101">
    <name>Gaming Mouse</name>
    <price currency="EUR">49.99</price>
    <description><![CDATA[Fast & <b>precise</b>]]></description>
    <stock/>
    <categories>
        <category>56</category>
        <category>65</category>
    </categories>
</product>
```

```php
[
    '@id' => '101',
    'name' => 'Gaming Mouse',
    'price' => ['@currency' => 'EUR', '#' => '49.99'],
    'description' => 'Fast & <b>precise</b>',
    'stock' => '',
    'categories' => ['category' => ['56', '65']],
]
```

**Namespaces**

Namespace prefixes are registered in `XmlOptions` and are independent of the prefixes used in the document:

- Path segments with a prefix (e.g. `atom:entry`) only match elements of the registered namespace, also if the document
  uses a different prefix or a default namespace. Path segments without prefix match the local name in any namespace.
- Elements and attributes of registered namespaces use the registered prefix in the output. All other names are taken
  from the document.

```php
// <rss xmlns:google="http://base.google.com/ns/1.0"><channel><item><google:id>1</google:id></item></channel></rss>
$iterator = new XmlIterator(
  path: ['rss', 'channel'],
  xmlOptions: new XmlOptions(namespaces: ['g' => 'http://base.google.com/ns/1.0'])
);

// ['g:id' => '1']
```

To output all names without namespace prefix, set the `XmlOptions::STRIP_NAMESPACES` flag. Registered namespaces are
still used to match the path.

```php
$iterator = new XmlIterator(
  path: ['rss', 'channel'],
  xmlOptions: new XmlOptions([], XmlOptions::STRIP_NAMESPACES)
);

// ['id' => '1']
```

> Elements with the same local name in different namespaces can no longer be distinguished and are merged into a list,
> e.g. `<title>` and `<g:title>` become `'title' => ['…', '…']`.

### Middleware

Every iterator takes an optional list of middlewares. Each record passes all middlewares in the given order before it
is returned. The value returned by a middleware is passed to the next one. Records skipped by `offset` or beyond
`limit` are not processed.

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;
use Xchert\FileReader\Middleware\MiddlewareInterface;

class TrimMiddleware implements MiddlewareInterface
{
    public function process(mixed $record): mixed
    {
        return \array_map(\trim(...), $record);
    }
}

$iterator = new CsvIterator(
  csvOptions: new CsvOptions(),
  middlewares: [new TrimMiddleware()]
);
```

For simple cases, `Middleware` wraps any callable:

```php
<?php

use Xchert\FileReader\Middleware\Middleware;

$iterator = new CsvIterator(
  csvOptions: new CsvOptions(),
  middlewares: [new Middleware(static fn (array $record) => \array_map(\trim(...), $record))]
);
```

#### ListElementConverter

XML cannot distinguish a list with a single element from a single value. The `ListElementConverter` uses an object
schema ([xchert/object-schema](https://github.com/x-chert/object-schema)) to ensure that `list` properties are always
returned as lists when reading XML data.

```php
<?php

use Xchert\FileReader\Xml\Middleware\ListElementConverter;
use Xchert\FileReader\Xml\XmlIterator;
use Xchert\Schema\SchemaRegistry;

$registry = SchemaRegistry::fromArray([
    [
        'name' => 'product',
        'properties' => [
            ['name' => 'tag', 'type' => 'list<string>'],
            ['name' => 'categories', 'type' => 'list<string>'],
        ],
    ],
]);

$iterator = new XmlIterator(
  path: ['inventory'],
  middlewares: [new ListElementConverter($registry, 'product')]   // schema of each record; nested object<…> types are resolved by the registry
);

// <product id="1"><tag>new</tag><categories><category>56</category></categories></product>
// ['@id' => '1', 'tag' => ['new'], 'categories' => ['56']]
```

- A single element is wrapped into a list, an empty element becomes `[]`.
- A wrapper element with a single child name (e.g. `<categories><category>…</category></categories>`) is unwrapped.
  For lists of objects, a single key that is a property of the item schema is treated as a single object.
- Lists within nested `object<…>` and list items are converted as well. All other values are returned unchanged.
- Elements whose nested schema is not found in the registry are returned unchanged.
