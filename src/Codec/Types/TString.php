<?php

namespace Codec\Types;

use Codec\Utils;

class TString extends ScaleInstance
{
    /**
     * @return mixed|void
     */
    public function decode()
    {
        $value = $this->nextBytes(gmp_intval($this->process('Compact')));
        return Utils::byteArray2String($value);
    }

    public function encode($param): string
    {
        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(strlen($param));
        return $length . Utils::string2Hex($param);
    }
}