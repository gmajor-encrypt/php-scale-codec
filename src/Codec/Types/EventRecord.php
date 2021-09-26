<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;

// https://substrate.dev/docs/en/knowledgebase/runtime/events
// https://github.com/paritytech/substrate/blob/master/frame/system/src/lib.rs#L787
//
// For struct EventRecord Record of an event happening.
// "EventRecord": {
//    "phase": "Phase",
//    "event": "E",
//    "topics": "Vec<T>"
// }

class EventRecord extends ScaleInstance
{
    public function decode (): array
    {
        if (is_null($this->metadata)) {
            throw new \InvalidArgumentException("Empty metadata, please fill metadata first");
        }

        $value = [];

        $value["phase"] = $this->process("U8");
        if ($value["phase"] == 0) {
            $value["extrinsic_index"] = $this->process("U32");
        }

        // look_up for metadata event_index, found event module, event id and params
        $value["look_up"] = Utils::bytesToHex($this->nextBytes(2));

        // check lookup event exist
        if (!array_key_exists($value["look_up"], $this->metadata["event_index"])) {
            throw new \InvalidArgumentException(sprintf("Not find Event Lookup %s, please check metadata info", $value["look_up"]));
        }

        $event = $this->metadata["event_index"][$value["look_up"]];
        $value["module_id"] = $event["module"]["name"];
        $value["event_id"] = $event["call"]["name"];

        $value["params"] = [];
        foreach ($event["call"]["args"] as $index => $argType) {
            array_push($value["params"], ["type" => $argType, "value" => $this->process($argType)]);
        }

        // topic
        $value["topic"] = $this->process("Vec<String>");

        return $value;
    }
}
