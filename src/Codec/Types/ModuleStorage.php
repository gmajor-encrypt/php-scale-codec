<?php


namespace Codec\Types;


class ModuleStorage extends ScaleInstance
{
    public function decode (): array
    {
        $value = [];
        $value["prefix"] = $this->process("String");
        $value["items"] = $this->process("Vec<MetadataModuleStorageEntry>");
        return $value;
    }
}
