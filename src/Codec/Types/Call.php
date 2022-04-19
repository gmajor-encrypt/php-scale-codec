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

    // call data encode
    public function encode ($param)
    {
        foreach (["module_id", "call_name", "params"] as $v) {
            if (!array_key_exists($v, $param)) {
                throw new \InvalidArgumentException(sprintf('call data %s not exist', $v));
            }
        }
        foreach ($this->metadata["call_index"] as $call_index => $call) {
            if ($call["module"]["name"] == $param["module_id"] and $call["call"]["name"] == $param["call_name"]) {
                $value = $call_index;
                foreach ($call["call"]["args"] as $index => $arg) {
                    $value = $value . $this->createTypeByTypeString($arg["type"])->encode($param["params"][$index]);
                }
                return $value;
            }
        }
        throw new \InvalidArgumentException(sprintf('Extrinsic %s not exist', $param["call_name"]));
    }
}
