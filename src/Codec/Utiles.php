<?php

namespace Codec;


class Utiles
{
    public static function string2ByteArray($string)
    {
        return unpack('C*', $string);
    }

    public static function byteArray2String($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        return join($chars);
    }

    /**
     * @param array $byteArray
     * @return string
     */
    public static function bytesToHex($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
    }

    /**
     * @param $hexString
     * @return array
     */
    public static function hexToBytes($hexString)
    {
        $string = hex2bin($hexString);
        $value = unpack('C*', $string);
        return is_array($value) ? array_values($value) : [];
    }

    public static function string2Hex($string)
    {
        return bin2hex($string);
    }

    public static function hex2String($hexString)
    {
        return hex2bin($hexString);
    }


    /**
     * @param $hexString string
     * @return string|string[]|null
     */
    public static function trimHex($hexString)
    {
        return preg_replace('/0x[0-9a-fA-F]/', '', $hexString);
    }


    /**
     * BytesToLittleInt
     * @param array $byteArray
     * @return int
     *
     *
     */
    public static function bytesToLittleInt(array $byteArray)
    {
        switch (count($byteArray)) {
            case 1:
                return $byteArray[0];
            case 2: // uint16
                return $byteArray[0] | $byteArray[1] << 8;
            case 4: // uint32
                return $byteArray[0] | $byteArray[1] << 8 | $byteArray[2] << 16 | $byteArray[3] << 24;
            case 8: // uint64
                return $byteArray[0] | $byteArray[1] << 8 | $byteArray[2] << 16 | $byteArray[3] << 24 |
                    $byteArray[4] << 32 | $byteArray[5] << 40 | $byteArray[6] << 48 | $byteArray[7] << 56;
        }
        return 0;
    }
}
