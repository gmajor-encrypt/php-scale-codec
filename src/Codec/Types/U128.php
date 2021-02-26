<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;
use BitWasp\Buffertools\Types\Uint128;
use BitWasp\Buffertools\Parser;

//TODO
class U128 extends Uint
{

    // todo
    function decode ()
    {
        $u128 = new Uint128();
        return $u128->read(new Parser(Utils::bytesToHex($this->nextBytes(16))));
//        return Utils::bytesToLittleInt($this->nextBytes(16));
    }

    function encode ($param)
    {
        $value = intval($param);

        if ($value >= 0 && $value <= 2 ** 128 - 1) {
            $u128 = new Uint128();
            return $u128->write($value);
        }
        return new \InvalidArgumentException(sprintf('%s range out U64', $value));
    }
}


