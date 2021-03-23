<?php

namespace Codec\Types;


use Codec\Utils;

class Metadata extends ScaleDecoder
{
    public $version;

    // only support metadata v12
    public $metadataVersion = [
        12 => "metadataV12"
    ];

    function decode ()
    {
        if (Utils::byteArray2String($this->nextBytes(4)) === "meta") {
            $this->version = hexdec(Utils::bytesToHex($this->nextBytes(1)));
//            echo $this->version;
            if (!empty($this->metadataVersion[$this->version])) {
                $this->metadata = $this->process($this->metadataVersion[$this->version]);
            } else {
                throw new \InvalidArgumentException(sprintf('only support metadata v12'));
            }
        } else {
            throw new \InvalidArgumentException(sprintf('decode runtime metadata fail'));
        }
    }
}
