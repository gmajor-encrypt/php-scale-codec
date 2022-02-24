<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use Codec\Utils;
use BitWasp\Buffertools\Types\Uint128;
use BitWasp\Buffertools\Parser;
use GMP;
use InvalidArgumentException;

// Basic integers are encoded using a fixed-width little-endian (LE) format.
// unsigned 128-bit integer
// https://substrate.dev/docs/en/knowledgebase/advanced/codec#fixed-width-integers

class U128 extends Uint
{

    public function decode(): GMP
    {
        $parser = new Parser(Utils::bytesToHex($this->nextBytes(16)));
        return $parser->readBytes(16, true)->getGmp();
    }

    public function encode($param)
    {
        $value = $param;
        if ($value >= 0 && gmp_cmp(strval($param), gmp_init("ffffffffffffffffffffffffffffffff", 16)) == -1) {
            $u128 = new Uint128(ByteOrder::LE);
            $buffer = new Buffer($u128->write(strval($value)));
            return Utils::trimHex($buffer->getHex());
        }
        throw new InvalidArgumentException(sprintf('%s range out U128', $value));
    }
}


