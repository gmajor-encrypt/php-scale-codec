<?php

namespace Codec\Types;


use Codec\Utils;

class metadataV12 extends ScaleInstance
{

    // Todo
    // metadataV12

    public function decode()
    {
        $result = [
            "metadata_version" => 12,
            "metadata" => null,
            "call_index" => null,
            "event_index" => null,
            "extrinsic" => null,
        ];

        $modules = $this->process("Vec<V12Module>");

        foreach ($modules as $index => $module) {
            foreach ($module["calls"] as $callIndex => $call) {
                $modules[$index]["calls"][$callIndex]["look_up"] = Utils::padLeft(dechex($index), 2) . Utils::padLeft(dechex($callIndex), 2);
                $result["call_index"][$modules[$index]["calls"][$callIndex]["look_up"]] = ["module" => $module, "call" => $call];
            }
            foreach ($module["events"] as $eventIndex => $event) {
                $modules[$index]["events"][$eventIndex]["look_up"] = Utils::padLeft(dechex($index), 2) . Utils::padLeft(dechex($eventIndex), 2);
                $result["event_index"][$modules[$index]["events"][$eventIndex]["look_up"]] = ["module" => $module, "call" => $event];
            }
        }

        $result["metadata"] = $modules;
        $extrinsic = $this->process("ExtrinsicMetadata");
        $result["extrinsic"] = $extrinsic;
    }
}
