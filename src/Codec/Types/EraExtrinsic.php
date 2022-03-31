<?php

namespace Codec\Types;

use Codec\Utils;

/**
 * Class EraExtrinsic
 *
 * @package Codec\Types
 *
 * The era for an extrinsic, indicating either a mortal or immortal extrinsic
 * The MortalEra for an extrinsic, indicating period and phase
 * immortal does not need to provide the life cycle of the transaction
 *
 */
class EraExtrinsic extends ScaleInstance
{
    function decode (): array
    {
        $optionData = $this->nextBytes(1);
        // immortal
        if ($optionData == "00") {
            return $optionData;
        }
        $eraU8a = Utils::hexToBytes(Utils::bytesToHex($optionData) . Utils::bytesToHex($this->nextBytes(1)));
        $encoded = intval($eraU8a[0]) + intval($eraU8a[1] << 8);
        $period = 2 << ($encoded % (1 << 4));
        $phase = ($encoded >> 4) * max($period >> 12, 1);
        return ["period" => $period, "phase" => $phase];
    }


    /**
     * EraExtrinsic encode
     *
     * @param $param
     * @return string
     * @throws \Exception
     */
    function encode ($param): string
    {
        if (is_string($param)) {
            if ($param == "00") {
                return $param;
            }
            return new \InvalidArgumentException("Invalid era value");
        }
        if (is_array($param) && array_key_exists("period", $param) && array_key_exists("phase", $param)) {
            $encoded = min(15, max(1, self::trailingZeros($param["period"]) - 1)) |
                (($param["phase"] / max($param["period"] >> 12, 1)) << 4);
            return Utils::LittleIntToHex(gmp_init($encoded), 2);
        }
        return new \InvalidArgumentException("Invalid era value");

    }

    /**
     * trailingZeros
     * Returns the number of trailing zeros in the binary representation of the given integer
     *
     * @param $value
     * @return int
     */
   private function trailingZeros ($value): int
    {
        $zero = 0;
        while (($value & 1) == 0) {
            $zero += 1;
            $value = $value >> 1;
        }
        return $zero;
    }
}