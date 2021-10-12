<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;

/**
 * Class Option
 *
 * @package Codec\Types
 *
 * One or zero values of a particular type
 *
 * https://substrate.dev/docs/en/knowledgebase/advanced/codec#options
 */
class Option extends ScaleInstance
{
    function decode ()
    {
        $optionData = $this->nextBytes(1);
        if (!empty($this->subType) && Utils::bytesToHex($optionData) != '00') {
            // As an exception, in the case that the type is a boolean, then it is always one byte
            if ($this->subType == "bool") {
                return Utils::bytesToHex($optionData) == '01';
            }
            return $this->process($this->subType, $this->data);
        }
        return null;
    }

    function encode ($param)
    {
        if ((!empty($param) or is_bool($param) or $param === 0 or $param === "0") and !empty($this->subType)) {
            if ($this->subType == "bool") {
                return $param == true ? "01" : "02";
            }
            $instant = $this->createTypeByTypeString($this->subType);
            return "01" . $instant->encode($param);
        }
        return "00";
    }
}