<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Int8;
use Codec\Utils;


// Basic integers are encoded using a fixed-width little-endian (LE) format.
// signed 8-bit integer
// https://substrate.dev/docs/en/knowledgebase/advanced/codec#fixed-width-integers
class  I8 extends TInt
{

    public function decode()
    {
        $i8 = new Int8(ByteOrder::LE);
        return $i8->read(new Parser(Utils::bytesToHex($this->nextBytes(1))));
    }

    public function encode($param)
    {
        $value = intval($param);

        if ($value >= -(2 ** 7) && $value <= 2 ** 7 - 1) {
            $i8 = new Int8(ByteOrder::LE);
            $buffer = new Buffer($i8->write($value));
            return Utils::trimHex($buffer->getHex());
        }
        throw new \InvalidArgumentException(sprintf('%s range out i8', $value));
    }

}