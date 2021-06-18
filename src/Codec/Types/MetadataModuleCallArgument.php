<?php

namespace Codec\Types;


class MetadataModuleCallArgument extends ScaleInstance
{
    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["type"] = $this->process("String");
        return $value;
    }
}
