<?php

declare(strict_types=1);

namespace Xchert\FileReader\Charset;

use Xchert\Util\Pod\Pod;
use Xchert\Util\Trait\FlagTrait;

class CharsetOptions extends Pod
{
    use FlagTrait;

    /** @var CharsetModifier[] */
    private array $modifiers = [];

    /** @param CharsetModifier[] $modifiers */
    public function __construct(
        protected string $encoding = 'UTF-8',
        array $modifiers = [],
        string ...$flags
    ) {
        foreach ($modifiers as $index => $modifier) {
            if (!$modifier instanceof CharsetModifier) {
                throw new \InvalidArgumentException(\sprintf('Modifier #%d must be instance of %s', $index, CharsetModifier::class));
            }
        }

        $this->modifiers = $modifiers;
        $this->setFlags(...$flags);
    }

    /** @return CharsetModifier[] */
    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }
}
