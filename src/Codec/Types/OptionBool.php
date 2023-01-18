<?php

namespace Codec\Types;

use Codec\Utils;

/**
 * Class OptionBool
 *
 * @package Codec\Types
 *
 * Special option structure
 *
 */
class OptionBool extends ScaleInstance
{
    function decode ()
    {
        $optionData = $this->nextBytes(1);
        if (Utils::bytesToHex($optionData) != '00') {
            return Utils::bytesToHex($optionData) == '01';
        }
        return null;
    }

    function encode ($param)
    {
        if (!is_null($param)) {
            return $param == true ? "01" : "02";
        }
        return "00";
    }
}