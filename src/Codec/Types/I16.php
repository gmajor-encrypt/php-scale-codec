<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Int16;
use Codec\Utils;
use InvalidArgumentException;

class I16 extends TInt
{

    public function decode()
    {
        $i16 = new Int16(ByteOrder::LE);
        return $i16->read(new Parser(Utils::bytesToHex($this->nextBytes(2))));
    }

    public function encode($param)
    {
        $value = intval($param);

        if ($value >= -(2 ** 15) && $value <= 2 ** 15 - 1) {
            $i16 = new Int16(ByteOrder::LE);
            $buffer = new Buffer($i16->write($value));
            return Utils::trimHex($buffer->getHex());
        }
        throw new InvalidArgumentException(sprintf('%s range out i16', $value));
    }

}