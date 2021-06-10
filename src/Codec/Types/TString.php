<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;
use SebastianBergmann\CodeCoverage\Util;

class TString extends ScaleInstance
{
    /**
     * @return mixed|void
     */
    function decode ()
    {
        $value = $this->nextBytes(gmp_intval($this->process('Compact')));
        return Utils::byteArray2String($value);
    }

    function encode ($param)
    {
        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(strlen($param));
        return $length . Utils::string2Hex($param);
    }
}