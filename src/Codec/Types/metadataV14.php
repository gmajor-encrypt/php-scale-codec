<?php

namespace Codec\Types;

use Codec\Generator;

/**
 * Class metadataV14
 * @package Codec\Types
 *
 *  metadata v14 pr  https://github.com/paritytech/substrate/pull/8615
 *  polkadot.js https://github.com/polkadot-js/api/blob/master/packages/types/src/interfaces/metadata/v14.ts
 *  scale.go https://github.com/itering/scale.go/blob/master/types/v14.go
 *  {
 *    "lookup": "PortableRegistry",
 *    "pallets": "Vec<PalletMetadataV14>",
 *    "extrinsic": "ExtrinsicMetadataV14"
 *  }
 */

class metadataV14 extends Struct
{

    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "lookup" => "PortableRegistry",
            "pallets" => "Vec<V14Module>",
            "extrinsic" => "ExtrinsicMetadataV14"
        ];
    }

    public function decode (): array
    {
        $metadataRaw = parent::decode();
        return $metadataRaw;
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}


