<?php

namespace Codec\Types;

use Codec\Utils;

/**
 * Class TString
 *
 * @package Codec\Types
 *
 * Strings are Vectors containing a valid UTF8 sequence.
 *
 * https://substrate.dev/docs/en/knowledgebase/advanced/codec#strings
 */
class TString extends ScaleInstance
{
    /**
     * @return mixed|void
     */
    public function decode ()
    {
        $value = $this->nextBytes(gmp_intval($this->process('Compact<u32>')));
        return Utils::byteArray2String($value);
    }

    public function encode ($param): string
    {
        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(strlen($param));
        return $length . Utils::string2Hex($param);
    }
}