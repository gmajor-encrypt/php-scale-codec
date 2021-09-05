<?php

namespace Codec\Types;

use Codec\Utils;

// https://substrate.dev/docs/en/knowledgebase/learn-substrate/extrinsics
// Extrinsic decode has signed or Unsigned Transactions
class Extrinsic extends ScaleInstance
{
    public function decode (): array
    {

        if (empty($this->metadata)) {
            throw new \InvalidArgumentException("Empty metadata, please fill metadata first");
        }

        $value = [];

        $value["extrinsic_length"] = gmp_intval($this->process("Compact<u32>"));

        if ($value["extrinsic_length"] != $this->remainBytesLength()) {
            $value["extrinsic_length"] = 0;
            $this->data->offset = 0;
        }

        $value["version"] = Utils::bytesToHex($this->nextBytes(1));
        $hasTransaction = hexdec($value["version"]) >= 80;

        // Extrinsic v4
        if (in_array($value["version"], ["04", "84"])) {
            // signed Transactions
            if ($hasTransaction) {
                $value["account_id"] = $this->process("address");
                $value["signature"] = $this->process("ExtrinsicSignature");
                $value["era"] = $this->process("EraExtrinsic");
                $value["nonce"] = gmp_strval($this->process("Compact<U64>"));

                if (in_array("ChargeTransactionPayment", $this->metadata["extrinsic"]["signedExtensions"])) {
                    $value["tip"] = gmp_strval($this->process("Compact<Balance>"));
                }
                $extrinsicHash = function () use ($value): string {
                    if ($value["extrinsic_length"] > 0) {
                        $extrinsicData = Utils::bytesToHex($this->data->data);
                    } else {
                        $instant = $this->createTypeByTypeString("Compact<u32>");
                        $extrinsicData = $instant->encode(count($this->data->data)) . Utils::bytesToHex($this->data->data);
                    }
                    return sprintf("0x%s", sodium_bin2hex(sodium_crypto_generichash(Utils::hex2String($extrinsicData))));
                };
                $value["extrinsic_hash"] = $extrinsicHash();
            }
            $value["look_up"] = Utils::bytesToHex($this->nextBytes(2));

        } else {
            throw new \InvalidArgumentException(sprintf("Extrinsic version %s is not support", $value["version"]));
        }

        $call = $this->metadata["call_index"][$value["look_up"]];
        if (is_null($call)) {
            throw new \InvalidArgumentException(sprintf("Not find Extrinsic Lookup %s, please check metadata info", $value["look_up"]));
        }
        $value["module_id"] = $call["module"]["name"];
        $value["call_name"] = $call["call"]["name"];

        $value["params"] = [];
        foreach ($call["call"]["args"] as $index => $arg) {
            $r = $this->process($arg["type"]);
            array_push($value["params"], [
                "name" => $arg["name"],
                "type" => $arg["type"],
                "value" => $r,
            ]);
        }

        return $value;
    }

}