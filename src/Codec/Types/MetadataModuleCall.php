<?php

namespace Codec\Types;

/**
 * Class MetadataModuleCall
 *
 * @package Codec\Types
 */
class MetadataModuleCall extends ScaleInstance
{
    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["args"] = $this->process("Vec<MetadataModuleCallArgument>");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }
}
