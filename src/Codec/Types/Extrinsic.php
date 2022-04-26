<?php

namespace Codec\Types;

use Codec\Generator;
use Codec\Utils;

// https://substrate.dev/docs/en/knowledgebase/learn-substrate/extrinsics
// Extrinsic decode has signed or Unsigned Transactions
// Unsigned
// {
//    "length" "Compact<u32>",
//    "Version" "u8"
//    "method": "Call"
// }
// Signed
// {
//    "length" "Compact<u32>",
//    "Version" "u8" // 84, V4, signing bit set
//    "signer":"account_id",
//    "signature": "EcdsaSignature | Ed25519Signature | Sr25519Signature"
//    "era" :"era",
//    "nonce": "Compact<U64>"
//    "method": "Call"
// }
class Extrinsic extends ScaleInstance
{

    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "extrinsic_length" => "Compact<u32>",
            "version" => "u8",
            "account_id" => "MultiAddress",
            "signature" => "ExtrinsicSignature",
            "era" => "EraExtrinsic",
            "nonce" => "Compact<U64>",
            "tip" => "Compact<Balance>",
            "look_up" => "[u8;2]",
            "call" => "Call"
        ];
    }

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
                $value["account_id"] = $this->process("MultiAddress");
                $value["signature"] = $this->process("ExtrinsicSignature");
                $value["era"] = $this->process("EraExtrinsic");
                $value["nonce"] = gmp_strval($this->process("Compact<U64>"));

                // check signedExtensions exist ChargeTransactionPayment
                $signedExtensions = $this->metadata["extrinsic"]["signedExtensions"];
                if (count($signedExtensions) > 0 && is_array($signedExtensions[0])) {
                    $signedExtensions = array_column($signedExtensions, "identifier");
                }
                if (in_array("ChargeTransactionPayment", $signedExtensions)) {
                    $value["tip"] = gmp_strval($this->process("Compact<Balance>"));
                }
                // generate extrinsic hash
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

        // check lookup
        if (!array_key_exists($value["look_up"], $this->metadata["call_index"])) {
            throw new \InvalidArgumentException(sprintf("Not find Extrinsic Lookup %s, please check metadata info", $value["look_up"]));
        }

        $call = $this->metadata["call_index"][$value["look_up"]];
        $value["module_id"] = $call["module"]["name"];
        $value["call_name"] = $call["call"]["name"];

        $value["params"] = [];
        foreach ($call["call"]["args"] as $arg) {
            $value["params"][] = [
                "name" => $arg["name"],
                "type" => $arg["type"],
                "value" => $this->process($arg["type"]),
            ];
        }

        return $value;
    }

    /**
     * Extrinsic encode
     *
     *
     * @param $param
     * @return string
     */
    function encode ($param): string
    {
        // check is signed or unsigned Extrinsic
        foreach (["version", "params"] as $v) {
            if (!array_key_exists($v, $param)) {
                throw new \InvalidArgumentException(sprintf('Extrinsic %s not exist', $param));
            }
        }

        // version
        $value = $param["version"];
        if (array_key_exists("signature", $param)) {
            foreach (["account_id", "era", "nonce"] as $v) {
                if (!array_key_exists($v, $param)) {
                    throw new \InvalidArgumentException(sprintf('Extrinsic %s not exist', $v));
                }
            }
            $value = $value . $this->createTypeByTypeString("MultiAddress")->encode($param["account_id"]);
            $value = $value . $this->createTypeByTypeString("ExtrinsicSignature")->encode($param["signature"]);
            $value = $value . $this->createTypeByTypeString("EraExtrinsic")->encode($param["era"]);
            $value = $value . $this->createTypeByTypeString("Compact<U64>")->encode($param["nonce"]);
            if (array_key_exists("tip", $param)) {
                $value = $value . $this->createTypeByTypeString("Compact<Balance>")->encode($param["tip"]);
            }
            // encode sign extrinsic
        }
        foreach ($this->metadata["call_index"] as $call_index => $call) {
            if ($call["module"]["name"] == $param["module_id"] and $call["call"]["name"] == $param["call_name"]) {
                $value = $value . $call_index;
                foreach ($call["call"]["args"] as $index => $arg) {
                    $value = $value . $this->createTypeByTypeString($arg["type"])->encode(
                            array_key_exists("value", $param["params"][$index]) ? $param["params"][$index]["value"] : $param["params"][$index]
                        );
                }
                return $this->createTypeByTypeString("Compact<u32>")->encode(count(Utils::hexToBytes($value))) . $value;
            }
        }
        throw new \InvalidArgumentException(sprintf('Extrinsic %s not exist', $param["call_name"]));
    }

}