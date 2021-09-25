<?php

namespace Codec\Types;


use Codec\ScaleBytes;
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
        13 => "metadataV13",
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
        $magicBytes = $this->nextBytes(4);
        if (Utils::byteArray2String($magicBytes) === "meta") {
            $this->version = hexdec(Utils::bytesToHex($this->nextBytes(1)));
            if (!empty($this->metadataVersion[$this->version])) {
                $metadata["metadata"] = $this->process($this->metadataVersion[$this->version]);
                $metadata["magicNumber"] = $this->process("u32", new ScaleBytes($magicBytes));
                $metadata["metadata_version"] = $this->version;
                return $metadata;
            } else {
                throw new InvalidArgumentException(sprintf('only support metadata v12,v13'));
            }
        } else {
            throw new InvalidArgumentException(sprintf('decode runtime metadata fail'));
        }
    }

    /**
     * metadata encode
     *
     * @param $param
     * @return mixed|void|null
     *  {
     *      "magicNumber": 1635018093,
     *      "metadata":{
     *          "modules":
     *          "extrinsic": []
     *      }
     *
     *  }
     */


    public function encode ($param)
    {
        if (!array_key_exists("magicNumber", $param) || !array_key_exists("metadata", $param) || !array_key_exists("metadata_version", $param)) {
            throw new InvalidArgumentException("invalid metadata");
        }
        // magicNumber u32
        $magicNum = $this->createTypeByTypeString("u32")->encode($param["magicNumber"]);

        // Version
        if (!array_key_exists($param["metadata_version"], $this->metadataVersion)) {
            throw new InvalidArgumentException(sprintf('only support metadata v12,v13'));
        }
        $metadataVersion = $this->metadataVersion[$param["metadata_version"]];

        // metadata module
        $module = $this->createTypeByTypeString($metadataVersion)->encode($param["metadata"]);

        return "0x" . $magicNum . Utils::LittleIntToBytes($param["metadata_version"], 1) . $module;
    }
}
