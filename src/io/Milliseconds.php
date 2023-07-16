<?php

declare(strict_types=1);

namespace bomberman\io;

class Milliseconds
{
    public function get(): float|int
    {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }
}
