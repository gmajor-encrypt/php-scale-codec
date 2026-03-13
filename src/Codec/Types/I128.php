<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Int128;
use Codec\Utils;


// Basic integers are encoded using a fixed-width little-endian (LE) format.
// signed 128-bit integer
// https://substrate.dev/docs/en/knowledgebase/advanced/codec#fixed-width-integers
class I128 extends TInt
{

    public function decode()
    {
        $i64 = new Int128(ByteOrder::LE);
        return $i64->read(new Parser(Utils::bytesToHex($this->nextBytes(16))));
    }

    public function encode($param)
    {
        $value = $param;
        try {
            $i128 = new Int128(ByteOrder::LE);
            $buffer = new Buffer($i128->write($value));
            return Utils::trimHex($buffer->getHex());
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException(sprintf('%s range out i128', $value));
        }
    }

}