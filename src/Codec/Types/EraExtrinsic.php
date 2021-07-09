<?php

namespace Codec\Types;

use Codec\Utils;

class EraExtrinsic extends ScaleInstance
{
    function decode (): array
    {
        $optionData = $this->nextBytes(1);
        if ($optionData == "00") {
            return $optionData;
        }
        $eraU8a = Utils::hexToBytes(Utils::bytesToHex($optionData) . Utils::bytesToHex($this->nextBytes(1)));

        $encoded = intval($eraU8a[0]) + intval($eraU8a[1] << 8);
        $period = 2 << ($encoded % (1 << 4));
        $phase = ($encoded >> 4) * max($period >> 12, 1);
        return ["period" => $period, "phase" => $phase];
    }

    function encode ($param)
    {
        if (empty($param) || $param == "00") {
            // todo
        }
        return "00";
    }
}