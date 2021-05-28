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
        $parser = new Parser(Utils::bytesToHex($this->nextBytes(16)));
        return $parser->readBytes(16)->getGmp();
    }

    function encode ($param)
    {
        $value = $param;
        if ($value >= 0 && gmp_cmp(strval($param), gmp_init("ffffffffffffffffffffffffffffffff", 16)) == -1) {
            $u128 = new Uint128(ByteOrder::LE);
            $buffer = new Buffer($u128->write($value));
            return Utils::trimHex($buffer->getHex());
        }
        throw new \InvalidArgumentException(sprintf('%s range out U128', $value));
    }
}


