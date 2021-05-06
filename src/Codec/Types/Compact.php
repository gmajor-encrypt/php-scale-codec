<?php

namespace Codec\Types;

use Codec\ScaleBytes;
use Codec\Utils;
use GMP;

class Compact extends ScaleInstance
{
    /**
     * @var integer $compactLength
     */
    protected $compactLength;

    /**
     * @var array $compactBytes
     */
    protected $compactBytes;

    /**
     * @return array|int|mixed
     */
    public function decode ()
    {
        self::checkCompactBytes();
        $UIntBitLength = 8 * $this->compactLength;
        if ($this->compactLength > 8) {
            foreach (range(4, 67) as $i) {
                if ($UIntBitLength >= 2 ** ($i - 1) && $UIntBitLength < 2 ** $i) {
                    $UIntBitLength = 2 ** ($i - 1);
                    break;
                }
            }
        }
        $data = $this->process("U{$UIntBitLength}", new ScaleBytes($this->compactBytes));
        if (is_int($data) && $this->compactLength <= 4) {
            return intval($data / 4);
        } else {
            return $data;
        }
    }


    /**
     * checkCompactBytes
     */
    protected function checkCompactBytes ()
    {
        $compactBytes = $this->nextBytes(1);
        if (count($compactBytes) == 0) {
            throw new \OutOfRangeException('OutOfRangeException Compact');
        }
        $mod = $compactBytes[0] % 4;

        switch ($mod) {
            case  0:
                $this->compactLength = 1;
                break;
            case 1:
                $this->compactLength = 2;
                break;
            case 2:
                $this->compactLength = 4;
                break;
            default:
                $this->compactLength = intval(5 + ($compactBytes[0] - 3) / 4);
        }

        switch ($this->compactLength) {
            case 1:
                $this->compactBytes = $compactBytes;
                break;
            case in_array($this->compactLength, [2, 4]):
                array_push($compactBytes, ...$this->nextBytes($this->compactLength - 1));
                $this->compactBytes = $compactBytes;
                break;
            default:
                $this->compactBytes = $this->nextBytes($this->compactLength - 1);
        }
    }


    /**
     * Compact encode
     *
     * @param GMP|string|int $param
     * @return \OutOfRangeException|string|null
     * @throws \Exception
     */
    public function encode ($param)
    {
        $value = $param;
        if (gettype($value) == "double") {
            throw new \InvalidArgumentException("value must be of type GMP|string|int, float given");
        }
        if (gmp_cmp($value, "64") == -1) {
            return Utils::LittleIntToBytes($value << 2, 1);
        } elseif (gmp_cmp($value, "16384") == -1) {
            return Utils::LittleIntToBytes($value << 2 | 1, 2);
        } elseif (gmp_cmp($value, "1073741824") == -1) {
            return Utils::LittleIntToBytes($value << 2 | 2, 4);
        } elseif (gmp_cmp($value, gmp_pow("2", 535)) == -1) {
            foreach (range(4, 67) as $i) {
                if (gmp_cmp($value, gmp_pow("2", 8 * ($i - 1))) != -1 && gmp_cmp($value, gmp_pow("2", 8 * $i)) == -1) {
                    return Utils::LittleIntToBytes(($i - 4) << 2 | 3, 1) . Utils::LittleIntToHex($value, $i);
                }
            }
        } else {
            throw new \OutOfRangeException('Compact encode out of range');
        }
    }

}