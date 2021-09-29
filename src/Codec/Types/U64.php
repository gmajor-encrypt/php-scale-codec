<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Uint64;
use Codec\Utils;
use InvalidArgumentException;

// Basic integers are encoded using a fixed-width little-endian (LE) format.
// unsigned 64-bit integer
// https://substrate.dev/docs/en/knowledgebase/advanced/codec#fixed-width-integers

class U64 extends Uint
{
    public function decode()
    {
        $u128 = new Uint64(ByteOrder::LE);
        return $u128->read(new Parser(Utils::bytesToHex($this->nextBytes(8))));
    }

    public function encode($param)
    {
        if ($param >= 0 && gmp_cmp(strval($param), "18446744073709551615") == -1) {
            $u64 = new Uint64(ByteOrder::LE);
            $buffer = new Buffer($u64->write($param));
            return Utils::trimHex($buffer->getHex());
        }
        throw new InvalidArgumentException(sprintf('%s range out U64', $param));
    }
}


