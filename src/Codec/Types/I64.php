<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Int64;
use Codec\Utils;

class I64 extends TInt
{

    function decode ()
    {
        $i64 = new Int64(ByteOrder::LE);
        return $i64->read(new Parser(Utils::bytesToHex($this->nextBytes(8))));
    }

    function encode ($param)
    {
        $value = strval($param);

        if (gmp_cmp($value, "-9223372036854775808") == 1 && gmp_cmp($value, "9223372036854775808") == -1) {
            $i64 = new Int64(ByteOrder::LE);
            $buffer = new Buffer($i64->write($value));
            return Utils::trimHex($buffer->getHex());
        }
        throw new \InvalidArgumentException(sprintf('%s range out i64', $value));
    }

}