<?php

namespace Codec\Types;

use Codec\ScaleBytes;
use Codec\Utils;

class Compact extends ScaleDecoder
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
    public function decode()
    {
        self::checkCompactBytes();
        if (!empty($this->subType)) {
            $data = $this->process($this->subType, new ScaleBytes($this->compactBytes));
            if (is_int($data) && $this->compactLength <= 4) {
                return intval($data / 4);
            } else {
                return $data;
            }
        }
        return $this->compactBytes;
    }


    /**
     * checkCompactBytes
     */
    protected function checkCompactBytes()
    {
        $compactBytes = $this->nextBytes(1);
        if (count($compactBytes) == 0) {
            return new \OutOfRangeException(sprintf('OutOfRangeException Compact'));
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
     * @param mixed $param
     * @return \OutOfRangeException|string|null
     */
    public function encode($param)
    {
        $value = intval($param);
        if ($value < 2**6) {
            return Utils::LittleIntToBytes($value<<2, 1);
        } elseif ($value < 2**14) {
            return Utils::LittleIntToBytes($value<<2 | 1, 2);
        }elseif ($value<2**30){
            return Utils::LittleIntToBytes($value<<2 | 2, 4);
        }elseif($value<2**536) {
            foreach (range(4, 67) as $i) {
                if( 2 ** (8 * ($i - 1)) <= $value && $value< 2 ** (8 * $i)){
                    return Utils::LittleIntToBytes(($i-4)<<2 | 3, 1) . Utils::LittleIntToBytes($value, $i);
                }

            }

        }else{
            return new \OutOfRangeException(sprintf('Compact encode'));
        }
    }

}