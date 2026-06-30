<?php

namespace Xchert\FileReader\Io;

$streamFilters = \stream_get_filters();

if(!\in_array('bom_filter', $streamFilters, true)) {
    \stream_filter_register('bom_filter', BomFilter::class);
}
