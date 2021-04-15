<?php

namespace Codec\Types;


class V12Module extends ScaleInstance
{
    public $name;

    public $prefix;

    public $storage;

    public $calls;

    public $events;

    public $constants;

    public $errors;

    public $index;


    public function decode ()
    {
        $this->name = $this->process("String");

        $storage = $this->process("Option<Vec<ModuleStorage>>");
        if (!empty($storage)) {
            $this->storage = $storage["items"];
            $this->prefix = $storage["prefix"];
        }

        $calls = $this->process("Option<Vec<MetadataModuleCall>>");
        if (!empty($calls)) {
            $this->calls = $calls;
        }

        $events = $this->process("Option<Vec<MetadataModuleEvent>>");
        if (!empty($events)) {
            $this->events = $events;
        }

        $constants = $this->process("Vec<ModuleConstants>");
        if (!empty($constants)) {
            $this->constants = $constants;
        }

        $errors = $this->process("Vec<MetadataModuleError>");
        if (!empty($errors)) {
            $this->errors = $errors;
        }

        $this->index = $this->process("U8");

        return [
            "name" => $this->name,
            "prefix" => $this->prefix,
            "calls" => $this->calls,
            "events" => $this->events,
            "errors" => $this->errors,
            "constants" => $this->constants,
            "index" => $this->index,
        ];
    }
}


