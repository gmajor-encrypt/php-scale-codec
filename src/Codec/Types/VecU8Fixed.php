<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

/**
 * Fixed length u8 vendor
 *
 * Class VecU8Fixed
 * @package Codec\Types
 */
class VecU8Fixed extends ScaleInstance
{

    function decode (): string
    {
        return Utils::bytesToHex($this->nextBytes($this->FixedLength));
    }


    /**
     * @param array $param
     * @return string
     */
    function encode ($param): string
    {
        $value = "";
        if (is_string($param) && ctype_xdigit(Utils::trimHex($param))) {
            $param = Utils::hexToBytes(Utils::trimHex($param));
        }
        if (!is_array($param)) {
            throw new InvalidArgumentException('param not array');
        }
        foreach ($param as $item) {
            $value .= $this->createTypeByTypeString("U8")->encode($item);
        }
        return $value;
    }
}