<?php

namespace Codec\Types;


use Codec\Utils;
use InvalidArgumentException;

// https://substrate.dev/docs/en/knowledgebase/runtime/metadata#metadata-formats
// only support metadata v12,v13, because the past metadata format has expired
// todo metadata v14  https://github.com/paritytech/substrate/pull/8615 when released
class Metadata extends ScaleInstance
{
    public $version;

    public $metadataVersion = [
        12 => "metadataV12",
        13 => "metadataV12",
//      14 => "metadataV14",  // todo
    ];

    /**
     * metadata decode
     * raw metadata can be query from substrate rpc state_getMetadata
     * https://substrate.dev/docs/en/knowledgebase/runtime/metadata#http--websocket-apis
     *
     * @return mixed
     */
    public function decode ()
    {
        if (Utils::byteArray2String($this->nextBytes(4)) === "meta") {
            $this->version = hexdec(Utils::bytesToHex($this->nextBytes(1)));
            if (!empty($this->metadataVersion[$this->version])) {
                $metadata = $this->process($this->metadataVersion[$this->version]);
                $metadata["metadata_version"] = $this->version;
                return $metadata;
            } else {
                throw new InvalidArgumentException(sprintf('only support metadata v12,v13'));
            }
        } else {
            throw new InvalidArgumentException(sprintf('decode runtime metadata fail'));
        }
    }
}
