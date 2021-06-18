<?php

namespace Codec\Types;

class MetadataModuleConstants extends ScaleInstance
{
    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["type"] = $this->process("String");
        $value["value"] = $this->process("Bytes");
        $value["docs"] = $this->process("vec<string>");
        return $value;
    }
}
