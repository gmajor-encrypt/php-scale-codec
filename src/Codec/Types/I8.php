<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Int8;
use Codec\Utils;

class I8 extends TInt
{

    function decode ()
    {
        $i8 = new Int8(ByteOrder::LE);
        return $i8->read(new Parser(Utils::bytesToHex($this->nextBytes(1))));
    }

    function encode ($param)
    {
        $value = intval($param);

        if ($value >= -(2 ** 7) && $value <= 2 ** 7 - 1) {
            $i8 = new Int8(ByteOrder::LE);
            $buffer = new Buffer($i8->write($value));
            return Utils::trimHex($buffer->getHex());
        }
        return new \InvalidArgumentException(sprintf('%s range out i8', $value));
    }

}