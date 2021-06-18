<?php

namespace Codec\Types;

class MetadataModuleError extends ScaleInstance
{
    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }
}
