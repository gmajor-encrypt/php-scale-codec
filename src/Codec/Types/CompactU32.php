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
            return intval(unpack("V", Utiles::bytesToHex($this->compactBytes))[1] / 4);
        } else {
            return unpack("V", Utiles::bytesToHex($this->compactBytes))[1];
        }
    }
}