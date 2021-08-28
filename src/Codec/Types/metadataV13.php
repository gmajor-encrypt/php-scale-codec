<?php

namespace Codec\Types;

class metadataV13 extends metadataV12
{
    // https://github.com/paritytech/substrate/pull/8635
    // metadataV13 update MetadataModuleStorageEntry, StorageFunctionType enum add StorageNMap
    // Since only enum adds a new index, it can be supported in v12 StorageFunctionType
    // https://github.com/gmajor-encrypt/php-scale-codec/blob/master/src/Codec/Types/MetadataModuleStorageEntry.php#L45

}


