<?php

declare(strict_types=1);

namespace Xchert\FileReader\Io;

/**
 * Removes a UTF-8 byte order mark at the start of the stream. Later occurrences are part of the data and kept.
 */
class BomFilter extends \php_user_filter
{
    private const string BOM = "\xef\xbb\xbf";

    private bool $checked = false;

    /** Start of the stream, buffered until it can be decided whether it is a byte order mark */
    private string $buffer = '';

    public function filter($in, $out, &$consumed, $closing): int
    {
        $passed = false;

        while ($bucket = \stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;

            if (!$this->checked) {
                $this->buffer .= $bucket->data;

                // The byte order mark may be split across buckets
                if (\strlen($this->buffer) < \strlen(self::BOM) && \str_starts_with(self::BOM, $this->buffer)) {
                    continue;
                }

                $bucket->data = $this->removeBom();
            }

            \stream_bucket_append($out, $bucket);
            $passed = true;
        }

        // Stream ends with an incomplete byte order mark, e.g. a single "\xef"
        if ($closing && !$this->checked && $this->buffer !== '') {
            \stream_bucket_append($out, \stream_bucket_new($this->stream, $this->removeBom()));
            $passed = true;
        }

        return $passed || $closing ? \PSFS_PASS_ON : \PSFS_FEED_ME;
    }

    private function removeBom(): string
    {
        $data = \str_starts_with($this->buffer, self::BOM) ? \substr($this->buffer, \strlen(self::BOM)) : $this->buffer;

        $this->checked = true;
        $this->buffer = '';

        return $data;
    }
}
