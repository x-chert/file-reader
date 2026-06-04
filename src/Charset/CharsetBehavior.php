<?php

namespace Xchert\FileReader\Charset;

enum CharsetBehavior: string
{
    case Translit = 'translit';
    case Ignore = 'ignore';
    case Error = 'error';

    public function getModifier(): ?string
    {
        return match ($this) {
            self::Translit => 'TRANSLIT',
            self::Ignore => 'IGNORE',
            self::Error => null,
        };
    }
}
