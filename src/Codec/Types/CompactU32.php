<?php

namespace Codec\Types;

use Codec\Utiles;

class CompactU32 extends \Compact
{

    /**
     * @return int
     */
    public function decode()
    {
        self::checkCompactBytes();
        if ($this->compactLength <= 4) {
            return intval(Utiles::bytesToLittleInt($this->compactBytes) / 4);
        } else {
            return Utiles::bytesToLittleInt($this->compactBytes);
        }
    }
}