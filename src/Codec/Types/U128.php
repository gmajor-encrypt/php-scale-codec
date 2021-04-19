<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use Codec\Utils;
use BitWasp\Buffertools\Types\Uint128;
use BitWasp\Buffertools\Parser;

class U128 extends Uint
{

    function decode ()
    {
        $u128 = new Uint128(ByteOrder::LE);
        return $u128->read(new Parser(Utils::bytesToHex($this->nextBytes(16))));
    }

    function encode ($param)
    {
        $value = intval($param);

        if ($value >= 0 && $value <= 2 ** 128 - 1) {
            $u128 = new Uint128(ByteOrder::LE);
            $buffer = new Buffer($u128->write($value));
            return Utils::trimHex($buffer->getHex());
        }
        throw new \InvalidArgumentException(sprintf('%s range out U128', $value));
    }
}


