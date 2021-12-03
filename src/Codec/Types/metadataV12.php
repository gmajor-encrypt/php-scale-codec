<?php

namespace Codec\Types;


use Codec\Generator;
use Codec\Utils;

class metadataV12 extends Struct
{

    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "pallets" => "Vec<V12Module>",
            "extrinsic" => "ExtrinsicMetadata"
        ];
    }

    public function decode (): array
    {
        $result = [
            "pallets" => null,
            "call_index" => null,
            "event_index" => null,
            "extrinsic" => null,
        ];

        $modules = $this->process("Vec<V12Module>");

        foreach ($modules as $index => $module) {
            foreach ($module["calls"] as $callIndex => $call) {
                $modules[$index]["calls"][$callIndex]["look_up"] = Utils::padLeft(dechex($module["index"]), 2) . Utils::padLeft(dechex($callIndex), 2);
                $result["call_index"][$modules[$index]["calls"][$callIndex]["look_up"]] = ["module" => $module, "call" => $call];
            }
            foreach ($module["events"] as $eventIndex => $event) {
                $modules[$index]["events"][$eventIndex]["look_up"] = Utils::padLeft(dechex($module["index"]), 2) . Utils::padLeft(dechex($eventIndex), 2);
                $result["event_index"][$modules[$index]["events"][$eventIndex]["look_up"]] = ["module" => $module, "call" => $event];
            }
        }

        $result["pallets"] = $modules;
        $result["extrinsic"] = $this->process("ExtrinsicMetadata");
        return $result;
    }


    public function encode ($param)
    {
        return parent::encode($param);
    }
}
