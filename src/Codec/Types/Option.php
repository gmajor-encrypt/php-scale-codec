<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;
use PhpOption\Some;

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
            $value = $this->process($this->subType, $this->data);
            return is_null($value) ? Some::create(null) : $value;
        }
        return null;
    }

    function encode ($param)
    {
        if (!is_null($param)) {
            $instant = $this->createTypeByTypeString($this->subType);
            if ($param instanceof Some) {
                $param = $param->get();
            }
            return "01" . $instant->encode($param);
        }
        return "00";
    }
}