<?php

namespace Codec\Types;

use Codec\Utils;

class CompactU32 extends Compact
{

    /**
     * @return int
     */
    public function decode()
    {
        self::checkCompactBytes();
        if ($this->compactLength <= 4) {
            return intval(Utils::bytesToLittleInt($this->compactBytes) / 4);
        } else {
            return Utils::bytesToLittleInt($this->compactBytes);
        }
    }
}