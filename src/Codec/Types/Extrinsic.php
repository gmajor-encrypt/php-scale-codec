<?php

namespace Codec\Types;

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
                if(count($signedExtensions)>0 && is_array($signedExtensions[0])){
                    $signedExtensions = array_column($signedExtensions,"identifier");
                }
                if (in_array("ChargeTransactionPayment",$signedExtensions )) {
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

}