<?php

namespace Codec\Types;


class V12Module extends ScaleDecoder
{
    public $name;

    public $prefix;

    public $call_index;

    public $storage;

    public $calls;

    public $events;

    public $constants;

    public $errors;

    public $index;


    public function decode ()
    {
        $this->name = $this->process("String");

        $storage = $this->process("Option<ModuleStorage>");
        if (!empty($storage)) {
            $this->storage = $storage["items"];
            $this->prefix = $storage["prefix"];
        }
    }
}


