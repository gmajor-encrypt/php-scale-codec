<?php

namespace Codec\Types;

class MetadataModuleEvent extends ScaleInstance
{
    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["args"] = $this->process("Vec<String>");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }
}
