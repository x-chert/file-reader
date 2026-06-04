<?php

namespace Xchert\FileReader;

use Xchert\FileReader\Charset\CharsetOptions;
use Xchert\FileReader\Exception\InvalidCharsetException;

class IoUtil
{
    public static function isValidEncoding(string $encoding): bool
    {
        if($encoding === '') {
            return false;
        }

        $encodings = \array_map('strtolower', \mb_list_encodings());

        if(\in_array(\strtolower($encoding), $encodings, true)) {
            return true;
        }

        \set_error_handler(fn() => true);
        $result = \iconv($encoding, $encoding, '');
        \restore_error_handler();

        return $result !== false;
    }

    /** @param resource $stream */
    public static function appendCharacterSetFilter($stream, CharsetOptions $options, string $toEncoding = 'UTF-8'): void
    {
        if(!\is_resource($stream)) {
            throw new \InvalidArgumentException(\sprintf('Stream must be resource. %s given', \get_debug_type($stream)));
        }

        if(!static::isValidEncoding($toEncoding)) {
            throw new InvalidCharsetException($toEncoding);
        }

        $fromEncoding = $options->getEncoding();

        if(!static::isValidEncoding($fromEncoding)) {
            throw new InvalidCharsetException($fromEncoding);
        }

        if(\strtolower($fromEncoding) === \strtolower($toEncoding)) {
            return;
        }

        $modifier = $options->getBehavior()->getModifier();

        if($modifier !== null) {
            $toEncoding .= '//'.$modifier;
        }

        $filterName = \sprintf('convert.iconv.%s/%s', $fromEncoding, $toEncoding);

        \stream_filter_append($stream, $filterName, \STREAM_FILTER_READ);
    }
}
