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
}
