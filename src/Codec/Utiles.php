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

    public static function hexToBytes($hexString)
    {
        $string = hex2bin($hexString);
        return unpack('C*', $string);
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
                return $byteArray[1];
            case 2: // uint16
                return $byteArray[1] | $byteArray[2] << 8;
            case 4: // uint32
                return $byteArray[1] | $byteArray[2] << 8 | $byteArray[3] << 16 | $byteArray[4] << 24;
            case 8: // uint64
                return $byteArray[1] | $byteArray[2] << 8 | $byteArray[3] << 16 | $byteArray[4] << 24 |
                    $byteArray[5] << 32 | $byteArray[6] << 40 | $byteArray[7] << 48 | $byteArray[8] << 56;
        }
        return 0;
    }
}
