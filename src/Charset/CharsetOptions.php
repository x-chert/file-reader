<?php

namespace Xchert\FileReader\Charset;

use Xchert\Util\Pod\Pod;

class CharsetOptions extends Pod
{
    public function __construct(
        protected string $encoding = 'UTF-8',
        protected CharsetBehavior $behavior = CharsetBehavior::Error
    ) {}

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function getBehavior(): CharsetBehavior
    {
        return $this->behavior;
    }
}
