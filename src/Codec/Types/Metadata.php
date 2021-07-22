<?php

namespace Codec\Types;


use Codec\Utils;
use InvalidArgumentException;

class Metadata extends ScaleInstance
{
    public $version;

    // only support metadata v12,v13
    public $metadataVersion = [
        12 => "metadataV12",
        13 => "metadataV12",
    ];

    public function decode ()
    {
        if (Utils::byteArray2String($this->nextBytes(4)) === "meta") {
            $this->version = hexdec(Utils::bytesToHex($this->nextBytes(1)));
            if (!empty($this->metadataVersion[$this->version])) {
                $metadata = $this->process($this->metadataVersion[$this->version]);
                $metadata["metadata_version"] = $this->version;
                return $metadata;
            } else {
                throw new InvalidArgumentException(sprintf('only support metadata v12'));
            }
        } else {
            throw new InvalidArgumentException(sprintf('decode runtime metadata fail'));
        }
    }
}
