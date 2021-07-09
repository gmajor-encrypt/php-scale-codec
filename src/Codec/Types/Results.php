<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

class Results extends ScaleInstance
{
    function decode ()
    {
        $optionData = $this->nextBytes(1);
        $subType = explode(",", $this->subType);
        if (count($subType) != 2) {
            throw new InvalidArgumentException(sprintf('%s not illegal', $this->subType));
        }
        if (Utils::bytesToHex($optionData) == '00') {
            return ["Ok" => $this->process(trim($subType[0]))];
        } elseif (Utils::bytesToHex($optionData) == '01') {
            return ["Err" => $this->process(trim($subType[1]))];
        }
        throw new InvalidArgumentException(sprintf('%s not illegal', Utils::bytesToHex($optionData)));
    }

    function encode ($param)
    {
        if (!is_array($param)) {
            return new InvalidArgumentException(sprintf('%v not array', $param));
        }
        $subType = explode(",", $this->subType);
        if (count($subType) != 2) {
            throw new InvalidArgumentException(sprintf('%s not illegal', $this->subType));
        }
        if (array_key_exists("Ok", $param)) {
            $subInstant = $this->createTypeByTypeString(trim($subType[0]));
            return "00" . $subInstant->encode($param["Ok"]);
        } elseif (array_key_exists("Err", $param)) {
            $subInstant = $this->createTypeByTypeString(trim($subType[1]));
            return "01" . $subInstant->encode($param["Err"]);
        }
        throw new InvalidArgumentException(sprintf('param not has ok or err key'));

    }
}