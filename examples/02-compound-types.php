<?php

/**
 * Example: Compound Types (Vec, Option, Tuple, Struct, Enum)
 * 
 * This example demonstrates encoding and decoding compound types.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Types\VecType;
use Substrate\ScaleCodec\Types\OptionType;
use Substrate\ScaleCodec\Types\TupleType;
use Substrate\ScaleCodec\Types\StructType;
use Substrate\ScaleCodec\Types\EnumType;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

echo "=== Compound Types Examples ===\n\n";

$registry = new TypeRegistry();

// ============================================
// Vec<T> - Vector/Array
// ============================================
echo "--- Vec<T> (Vector) ---\n\n";

// Vec<U8>
$vecU8 = (new VecType($registry))->setElementType($registry->get('U8'));

$encoded = $vecU8->encode([1, 2, 3, 4, 5]);
echo "Vec<U8> encode([1,2,3,4,5]): " . $encoded->toHex() . "\n";

$decoded = $vecU8->decode(ScaleBytes::fromHex('0x140102030405'));
echo "Vec<U8> decode: " . json_encode($decoded) . "\n";

// Vec<U32>
$vecU32 = (new VecType($registry))->setElementType($registry->get('U32'));
$encoded = $vecU32->encode([0, 1, 2, 3]);
echo "Vec<U32> encode([0,1,2,3]): " . $encoded->toHex() . "\n";

// Empty vector
$encoded = $vecU8->encode([]);
echo "Vec<U8> encode([]): " . $encoded->toHex() . " (empty)\n";

// ============================================
// Option<T> - Optional Value
// ============================================
echo "\n--- Option<T> ---\n\n";

$optionU8 = (new OptionType($registry))->setInnerType($registry->get('U8'));

// Some value
$encoded = $optionU8->encode(42);
echo "Option<U8> encode(42): " . $encoded->toHex() . " (Some)\n";

// None value
$encoded = $optionU8->encode(null);
echo "Option<U8> encode(null): " . $encoded->toHex() . " (None)\n";

// Option with decode
$decoded = $optionU8->decode(ScaleBytes::fromHex('0x012a'));
echo "Option<U8> decode('0x012a'): " . json_encode($decoded) . "\n";

$decoded = $optionU8->decode(ScaleBytes::fromHex('0x00'));
echo "Option<U8> decode('0x00'): " . json_encode($decoded) . " (null)\n";

// ============================================
// Tuple - Fixed-size heterogeneous sequence
// ============================================
echo "\n--- Tuple ---\n\n";

$tuple = (new TupleType($registry))
    ->addElementType($registry->get('U8'))
    ->addElementType($registry->get('U32'))
    ->addElementType($registry->get('Bool'));

$encoded = $tuple->encode([255, 1000000, true]);
echo "Tuple(U8,U32,Bool) encode([255,1000000,true]): " . $encoded->toHex() . "\n";

$decoded = $tuple->decode(ScaleBytes::fromHex('0xff40420f01'));
echo "Tuple decode: " . json_encode($decoded) . "\n";

// ============================================
// Struct - Named fields
// ============================================
echo "\n--- Struct ---\n\n";

$personStruct = (new StructType($registry))->setFields([
    'id' => $registry->get('U32'),
    'name' => $registry->get('String'),
    'active' => $registry->get('Bool'),
]);

$person = [
    'id' => 1,
    'name' => 'Alice',
    'active' => true,
];

$encoded = $personStruct->encode($person);
echo "Struct encode({id:1, name:'Alice', active:true}): " . $encoded->toHex() . "\n";

$decoded = $personStruct->decode(ScaleBytes::fromHex($encoded->toHex()));
echo "Struct decode: " . json_encode($decoded) . "\n";

// ============================================
// Enum - Tagged union
// ============================================
echo "\n--- Enum ---\n\n";

$resultEnum = new EnumType($registry);
$resultEnum->addVariant('Ok', 0, $registry->get('U32'));
$resultEnum->addVariant('Err', 1, $registry->get('String'));

// Ok variant
$encoded = $resultEnum->encode(['Ok' => 42]);
echo "Enum encode({Ok: 42}): " . $encoded->toHex() . "\n";

// Err variant
$encoded = $resultEnum->encode(['Err' => 'error message']);
echo "Enum encode({Err: 'error message'}): " . $encoded->toHex() . "\n";

// Unit variant
$optionEnum = new EnumType($registry);
$optionEnum->addVariant('None', 0);
$optionEnum->addVariant('Some', 1, $registry->get('U32'));

$encoded = $optionEnum->encode(['None' => null]);
echo "Enum encode({None}): " . $encoded->toHex() . "\n";

echo "\n=== Compound Types Complete ===\n";
