<?php

declare(strict_types=1);

namespace Xchert\FileReader\Xml;

use Xchert\Util\Pod\Pod;
use Xchert\Util\Trait\FlagTrait;

class XmlOptions extends Pod
{
    use FlagTrait;

    /** Output element and attribute names without namespace prefix */
    public const string STRIP_NAMESPACES = 'strip_namespaces';

    /** @param array<string, string> $namespaces Prefix => namespace URI */
    public function __construct(
        protected array $namespaces = [],
        string ...$flags
    ) {
        $this->validateNamespaces($namespaces);
        $this->setFlags(...$flags);
    }

    /** @return array<string, string> */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function getNamespaceUri(string $prefix): ?string
    {
        return $this->namespaces[$prefix] ?? null;
    }

    public function getPrefix(string $namespaceUri): ?string
    {
        $prefix = \array_search($namespaceUri, $this->namespaces, true);

        return $prefix === false ? null : $prefix;
    }

    private function validateNamespaces(array $namespaces): void
    {
        foreach ($namespaces as $prefix => $uri) {
            if (!\is_string($prefix) || $prefix === '' || \str_contains($prefix, ':')) {
                throw new \InvalidArgumentException(\sprintf('Namespace prefix "%s" must be a non-empty string without colon.', $prefix));
            }

            if (!\is_string($uri) || $uri === '') {
                throw new \InvalidArgumentException(\sprintf('Namespace URI for prefix "%s" must be a non-empty string.', $prefix));
            }
        }

        // The URI is mapped back to its prefix for output keys, so it has to be unique
        if (\count(\array_unique($namespaces)) !== \count($namespaces)) {
            throw new \InvalidArgumentException('Each namespace URI may only be mapped to one prefix.');
        }
    }
}
