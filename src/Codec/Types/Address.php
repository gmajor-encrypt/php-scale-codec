<?php


namespace Codec\Types;

use Codec\ScaleBytes;
use Codec\Types\ScaleDecoder;
use Codec\Utiles;


class Address extends ScaleDecoder
{
    public function decode()
    {
        $accountLength = $this->data->nextBytes(1);
        switch (Utiles::bytesToHex($accountLength)) {
            case "ff":
                $this->value = ["account_id" => Utiles::bytesToHex($this->data->nextBytes(32))];
                return;
            case "fc":
                $accountIndex = $this->data->nextBytes(2);
                break;
            case "fe":
                $accountIndex = $this->data->nextBytes(8);
                break;
            case "fd":
                $accountIndex = $this->data->nextBytes(4);
                break;
            default:
                $accountIndex = $accountLength;
        }
        $this->value = ["account_index" => Utiles::bytesToHex($accountIndex)];
    }

    public function encode()
    {
        return $this->data;
    }
}