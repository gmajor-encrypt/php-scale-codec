<?php

namespace Codec\Types;


class metadata extends ScaleDecoder
{

    // Todo
    // metadataV12

    function decode ()
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
                $modules[$index]["calls"][$callIndex]["look_up"] = "";
                $result["call_index"][$modules[$index]["calls"][$callIndex]["look_up"]] = ["module" => $module, "call" => $call];
            }
            foreach ($module["events"] as $eventIndex => $event) {
                $modules[$index]["events"][$eventIndex]["look_up"] = "";
                $result["event_index"][$modules[$index]["events"][$eventIndex]["look_up"]] = ["module" => $module, "call" => $event];
            }
        }

        $result["metadata"] = $modules;
        $extrinsic = $this->process("ExtrinsicMetadata");
        $result["extrinsic"] = $extrinsic;
    }
}
