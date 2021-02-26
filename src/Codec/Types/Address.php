<?php


namespace Codec\Types;

use Codec\ScaleBytes;
use Codec\Types\ScaleDecoder;
use Codec\Utils;


class Address extends ScaleDecoder
{
    public function decode ()
    {
        $accountLength = $this->data->nextBytes(1);
        switch (Utils::bytesToHex($accountLength)) {
            case "ff":
                $this->value = ["account_id" => Utils::bytesToHex($this->data->nextBytes(32))];
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
        $this->value = ["account_index" => Utils::bytesToHex($accountIndex)];
    }

    // todo
    function encode ($param)
    {

    }
}