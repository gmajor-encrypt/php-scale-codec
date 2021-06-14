<?php


namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;


class Address extends ScaleInstance
{
    public function decode(): string
    {
        $accountLength = $this->data->nextBytes(1);
        switch (Utils::bytesToHex($accountLength)) {
            case "ff":
                return Utils::bytesToHex($this->data->nextBytes(32));
            case "fc":
                $this->data->nextBytes(2);
                break;
            case "fe":
                $this->data->nextBytes(8);
                break;
            case "fd":
                $this->data->nextBytes(4);
                break;
        }
        return "";
    }


    public function encode($param): string
    {
        $value = Utils::trimHex($param);
        if (strlen($value) == 64) {
            return "ff" . $value;
        } else {
            throw new InvalidArgumentException(sprintf('Address not support AccountIndex or param not AccountId'));
        }
    }
}