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

### 1. Simple CSV File Iteration (With Flat Headers)

By default, `CsvOptions` uses standard `;` separation and flat header mapping, returning associative arrays where the
keys are derived from the header line.

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;

$csvOptions = new CsvOptions(); // Default delimiter ';', flat header enabled
$iterator = new CsvIterator($csvOptions);

// Iterate line by line
foreach ($iterator->iterateFile('path/to/data.csv') as $row) {
    // $row is an associative array: ['name' => 'John', 'email' => 'john@example.com']
    print_r($row);
}
```

### 2. Iterating with Offset and Limit (Pagination)

You can skip a specified number of records or limit the total number of records returned.

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;

$iterator = new CsvIterator(new CsvOptions());

$offset = 10; // Skip first 10 data rows
$limit = 50;  // Read next 50 data rows

foreach ($iterator->iterateFile('path/to/data.csv', $offset, $limit) as $row) {
    // Process paginated row
}
```

### 3. Custom Delimiters and Header Behaviors

You can customize the delimiter, enclosure, escape characters, and how headers are treated using `HeaderBehavior`.

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;
use Xchert\FileReader\Csv\HeaderBehavior;

$csvOptions = new CsvOptions(
    delimiter: ',',
    enclosure: '"',
    escape: '\\',
    headerBehavior: HeaderBehavior::NoHeader // Header lines treated as normal data
);

$iterator = new CsvIterator($csvOptions);

foreach ($iterator->iterateFile('path/to/data.csv') as $row) {
    // $row is an indexed array: [0 => 'Value 1', 1 => 'Value 2']
}
```

Available `HeaderBehavior` options:

- `HeaderBehavior::FlatHeader` *(default)*: Maps header row keys to each record row into a single-level associative
  array.
- `HeaderBehavior::NestedHeader`: Parses dot-notation header keys into nested associative arrays (using Symfony's
  CsvEncoder).
- `HeaderBehavior::SkipHeader`: Skips the first header row and returns indexed arrays for subsequent rows.
- `HeaderBehavior::NoHeader`: Treats all lines, including the first line, as data rows.

### 4. Handling Character Encodings (e.g., ISO-8859-1 to UTF-8)

Configure `CharsetOptions` to transparently convert streams from legacy encodings to UTF-8 or any specified output
encoding.

```php
<?php

use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;

$charsetOptions = new CharsetOptions(
    encoding: 'UTF-8'
);

$csvOptions = new CsvOptions();
$iterator = new CsvIterator($csvOptions, $charsetOptions);

foreach ($iterator->iterateFile('path/to/latin1_file.csv') as $row) {
    // Content is converted to UTF-8 automatically during iteration
}
```

### 5. Iterating Raw Strings or Streams

`CsvIterator` can process strings in memory or existing stream resources directly.

#### Iterating Raw Strings

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;

$csvData = "Name;Age\nAlice;30\nBob;25";

$iterator = new CsvIterator(new CsvOptions());

foreach ($iterator->iterateString($csvData) as $row) {
    print_r($row);
}
```

#### Iterating PHP Streams

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;

$stream = fopen('https://example.com/data.csv', 'r');

$iterator = new CsvIterator(new CsvOptions());

foreach ($iterator->iterateStream($stream) as $row) {
    print_r($row);
}

fclose($stream);
```

### 6. Skipping Empty Lines

Pass the `CsvIterator::SKIP_EMPTY` flag in `CsvOptions` to automatically filter out blank lines during iteration.

```php
<?php

use Xchert\FileReader\Csv\CsvIterator;
use Xchert\FileReader\Csv\CsvOptions;

$csvOptions = new CsvOptions(
    flags: [CsvIterator::SKIP_EMPTY]
);

$iterator = new CsvIterator($csvOptions);

foreach ($iterator->iterateFile('path/to/data.csv') as $row) {
    // Empty rows are automatically skipped
}
```
