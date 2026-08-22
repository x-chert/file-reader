<?php

declare(strict_types=1);

namespace Xchert\FileReader\Charset;

enum CharsetModifier: string
{
    case Translit = 'TRANSLIT';
    case Ignore = 'IGNORE';
}
