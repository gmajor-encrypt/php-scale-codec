<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;
use SebastianBergmann\CodeCoverage\Util;

class TString extends ScaleDecoder
{
    /**
     * @return mixed|void
     */
    function decode()
    {
        $value = $this->nextBytes($this->process('CompactU32'));
        return Utils::byteArray2String($value);
    }
}