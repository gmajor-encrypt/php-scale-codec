<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;

class EventRecord extends ScaleInstance
{
    public function decode (): array
    {
        if (empty($this->metadata)) {
            throw new \InvalidArgumentException("Empty metadata, please fill metadata first");
        }

        $value = [];
        $value["phase"] = $this->process("U8");
        if ($value["phase"] == 0) {
            $value["extrinsic_index"] = $this->process("U32");
        }
        $value["look_up"] = Utils::bytesToHex($this->nextBytes(2));

        $event = $this->metadata["event_index"][$value["look_up"]];
        if (is_null($event)) {
            throw new \InvalidArgumentException(sprintf("Not find Event Lookup %s, please check metadata info", $value["look_up"]));
        }
        $value["module_id"] = $event["module"]["name"];
        $value["event_id"] = $event["call"]["name"];

        $value["params"] = [];
        foreach ($event["call"]["args"] as $index => $argType) {
            array_push($value["params"], ["type" => $argType, "value" => $this->process($argType)]);
        }

        $event["topic"] = $this->process("Vec<String>");

        return $value;
    }
}
