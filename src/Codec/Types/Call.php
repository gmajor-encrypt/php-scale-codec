<?php

namespace Codec\Types;

use Codec\Utils;

/**
 * Class Call
 *
 * GenericCall/Call
 * Extrinsic function descriptor
 * {
 *  "lookup": "2bytes"
 *  "call": "CallInstance"
 * }
 *
 * @package Codec\Types
 */
class Call extends ScaleInstance
{
    /**
     * Call decode
     *
     * @return array
     */
    public function decode (): array
    {
        $lookUp = Utils::bytesToHex($this->nextBytes(2));
        if (empty($this->metadata)) {
            throw new \InvalidArgumentException("empty metadata");
        }
        $call = $this->metadata["call_index"][$lookUp];
        $value = [
            "module_id" => $call["module"]["name"],
            "call_name" => $call["call"]["name"],
            "params" => []
        ];
        foreach ($call["call"]["args"] as $arg) {
            $r = $this->process($arg["type"]);
            $value["params"][] = [
                "name" => $arg["name"],
                "type" => $arg["type"],
                "value" => $r,
            ];
        }
        return $value;
    }

    // Call encode todo
    public function encode ($param)
    {

    }
}
