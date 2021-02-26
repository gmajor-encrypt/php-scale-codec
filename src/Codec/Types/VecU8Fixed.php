<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class VecU8Fixed extends ScaleDecoder
{
    /**
     * @var $FixedLength
     */
    protected $FixedLength;

    function decode ()
    {
        return sprintf('%s', Utils::bytesToHex($this->nextBytes($this->FixedLength)));
    }


    /**
     * @param $param
     * @return mixed|string|null
     */
    function encode ($param)
    {
        $value = Utils::trimHex($param);
        if (strlen($value) != ($this->FixedLength) * 2) {
            return new \InvalidArgumentException(sprintf('%v not fixed width $v', $value, $this->FixedLength));
        }
        return $value;
    }
}