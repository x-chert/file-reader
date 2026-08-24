# xchert/file-reader

A memory-efficient and flexible PHP library for streaming and iterating files with built-in character set conversion

## Features

- **Memory Efficient:** Uses PHP Generators (`\Generator`) to iterate through large files without loading the entire
  file into memory.
- **Multiple Input Sources:** Process files, raw strings, or open stream resources seamlessly.
- **Charset Conversion:** Automatically handles character encoding transformations (e.g., ISO-8859-1 to UTF-8) during
  stream reading.
- **Pagination Support:** Built-in `offset` and `limit` support.

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
      escape: '\\',                                 // default
      headerBehavior: HeaderBehavior::FlatHeader,   // default
      flags: [CsvIterator::SKIP_EMPTY]              // optional; SKIP_EMPTY flag causes automatic filtering of empty lines
  ),
  charsetOptions: new CharsetOptions(               // optional; encoding of the csv data, defaults to UTF-8
      encoding: 'UTF-8'
  )
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
- `HeaderBehavior::NestedHeader`: Parses dot-notation header keys into nested associative arrays (using Symfony's
  CsvEncoder).
- `HeaderBehavior::SkipHeader`: Skips the first header row and returns indexed arrays for subsequent rows.
- `HeaderBehavior::NoHeader`: Treats all lines, including the first line, as data rows.

### Read XML data

The XmlIterator takes an array as path and reads all elements within this path.

```php
<?php

use Xchert\FileReader\Xml\XmlIterator;
use Xchert\FileReader\Charset\CharsetOptions;


$iterator = new XmlIterator(
  path: ['inventory', 'products']                   // required; takes an array of strings, can also be empty to read from the root element
  charsetOptions: new CharsetOptions(               // optional; encoding of the csv data, defaults to UTF-8
      encoding: 'UTF-8'
  )
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