<?php


namespace Codec\Types;

use Codec\Utils;

/**
 * Class Data
 * @package Codec\Types
 * Data: A raw byte array, with a length prefix.
 * Data is a raw byte array, with a length prefix. The length prefix is a U8 integer, which indicates the number of bytes in the array.
 *
 *
 * @package Codec\Types
 */
class Data extends ScaleInstance
{
    // "Null", "String", "H256", "H256", "H256", "H256"
    public array $types = [
        "Null",
        "String",
        "H256",
        "H256",
        "H256",
        "H256"
    ];
    // "None", "Raw", "BlakeTwo256", "Sha256", "Keccak256", "ShaThree256"
    public array $names = [
        "None",
        "Raw",
        "BlakeTwo256",
        "Sha256",
        "Keccak256",
        "ShaThree256"
    ];


    public function decode(): array
    {
        $index = $this->nextU8();
        if ($index == 0) {
            return ["None" => "NULL"];
        }
        // index >=1 && index <= 33
        if ($index >= 1 && $index <= 33) {
            $length = $index - 1;
            return [sprintf("Raw%d", $length) => Utils::bytesToHex($this->nextBytes($length))];
        }
        if ($index >= 34 && $index <= 37) {
            $length = $index - 32;
            return [sprintf("%s", $this->names[$length]) => Utils::add_hex(Utils::bytesToHex($this->nextBytes(32)))];
        }
        throw new \InvalidArgumentException(sprintf('Decode DATA index %s range out Data', $index));
    }


    public function encode($param): string
    {
        if (!is_array($param)) {
            throw new \InvalidArgumentException(sprintf('Encode DATA Error: %s is not array', $param));
        }
        $key = key($param);
        $value = current($param);

        $U8 = $this->createTypeByTypeString("U8");
        if ($key == "None") {
            return $U8->encode(0);
        }
        if (in_array($key, $this->types)) {
            if ($key == "Null") {
                return $U8->encode(0);
            }
            if (in_array($key, $this->names)) {
                $length = array_search($key, $this->names);
                return $U8->encode($length + 32+1) . $value;
            }
        }
        if (str_contains($key, "Raw")) {
            $length = intval(str_replace("Raw", "", $key));
            return $U8->encode($length+1) . $value;
        }
        throw new \InvalidArgumentException(sprintf('Encode DATA Error: %s is not support', $key));
    }
}