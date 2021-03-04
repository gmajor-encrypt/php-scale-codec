<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Int32;
use Codec\Utils;

class I32 extends TInt
{

    function decode ()
    {
        $i32 = new Int32(ByteOrder::LE);
        return $i32->read(new Parser(Utils::bytesToHex($this->nextBytes(4))));
    }

    function encode ($param)
    {
        $value = intval($param);

        if ($value >= -(2 ** 31) && $value <= 2 ** 31 - 1) {
            $i32 = new Int32(ByteOrder::LE);
            $buffer = new Buffer($i32->write($value));
            return Utils::trimHex($buffer->getHex());
        }
        return new \InvalidArgumentException(sprintf('%s range out i32', $value));
    }

}