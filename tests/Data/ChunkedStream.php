<?php

declare(strict_types=1);

namespace Xchert\FileReader\Test\Data;

/**
 * Stream wrapper returning a single byte per read, so stream filters receive one bucket per byte.
 */
final class ChunkedStream
{
    public const string PROTOCOL = 'chunked';

    /** @var array<string, string> */
    private static array $contents = [];

    /** @var resource|null */
    public $context;

    private string $data = '';

    private int $position = 0;

    /** @return resource */
    public static function open(string $content)
    {
        if (!\in_array(self::PROTOCOL, \stream_get_wrappers(), true)) {
            \stream_wrapper_register(self::PROTOCOL, self::class);
        }

        $name = \uniqid();
        self::$contents[$name] = $content;

        return \fopen(self::PROTOCOL.'://'.$name, 'r');
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        $this->data = self::$contents[\substr($path, \strlen(self::PROTOCOL) + 3)] ?? '';

        return true;
    }

    public function stream_read(int $count): string
    {
        return $this->data[$this->position++] ?? '';
    }

    public function stream_eof(): bool
    {
        return $this->position >= \strlen($this->data);
    }

    public function stream_stat(): array
    {
        return [];
    }
}
