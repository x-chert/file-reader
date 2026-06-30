<?php

namespace Xchert\FileReader\Charset;

use Xchert\Util\Pod\Pod;
use Xchert\Util\Trait\FlagTrait;

class CharsetOptions extends Pod
{
    use FlagTrait;

    public function __construct(
        protected string $encoding = 'UTF-8',
        protected CharsetBehavior $behavior = CharsetBehavior::Error,
        string ...$flags
    ) {
        $this->setFlags(...$flags);
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function getBehavior(): CharsetBehavior
    {
        return $this->behavior;
    }
}
