<?php

namespace Codec\Types;

use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\ByteOrder;
use BitWasp\Buffertools\Parser;
use BitWasp\Buffertools\Types\Int128;
use Codec\Utils;

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