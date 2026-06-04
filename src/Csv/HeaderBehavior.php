<?php

namespace Xchert\FileReader\Csv;

enum HeaderBehavior: string
{
    case FlatHeader = 'flat_header';
    case NestedHeader = 'nested_header';
    case NoHeader = 'no_header';
    case SkipHeader = 'skip_header';

    public function hasHeader(): bool
    {
        return $this !== self::NoHeader;
    }

    public function isUsingHeader(): bool
    {
        return \in_array($this, [self::FlatHeader, self::NestedHeader]);
    }
}
