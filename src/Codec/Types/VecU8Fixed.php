<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

class VecU8Fixed extends ScaleInstance
{

    function decode (): string
    {
        return Utils::bytesToHex($this->nextBytes($this->FixedLength));
    }


    /**
     * @param array $param
     * @return mixed|string|null
     */
    function encode ($param): string
    {
        $value = "";
        if (is_string($param) && ctype_xdigit($param)) {
            $param = Utils::hexToBytes($param);
        }
        if (!is_array($param)) {
            throw new InvalidArgumentException(sprintf('param not array'));
        }
        foreach ($param as $index => $item) {
            $value .= $this->createTypeByTypeString(sprintf("U8"))->encode($item);
        }
        return $value;
    }
}