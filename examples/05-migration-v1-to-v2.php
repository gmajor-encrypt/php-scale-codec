<?php

/**
 * Migration Examples: v1.x to v2.0
 * 
 * This file shows side-by-side comparisons of v1.x and v2.0 code.
 * Use this as a reference when migrating your application.
 */

// ============================================================
// SETUP COMPARISON
// ============================================================

echo "=== Setup Comparison ===\n\n";

// v1.x Code:
// ---------------------------------------------------------
// $codec = new \Substrate\Scale\Codec();
// $u32 = new \Substrate\Scale\Types\U32();
// ---------------------------------------------------------

// v2.0 Code:
use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

$registry = new TypeRegistry();
$u32 = $registry->get('U32');

echo "v1.x: new Codec(), new U32()\n";
echo "v2.0: new TypeRegistry(), \$registry->get('U32')\n\n";

// ============================================================
// ENCODING COMPARISON
// ============================================================

echo "=== Encoding Comparison ===\n\n";

// v1.x Code:
// ---------------------------------------------------------
// $codec = new Codec();
// $hex = $codec->encode(42, 'U32');
// echo $hex; // "0x2a000000"
// ---------------------------------------------------------

// v2.0 Code:
$u32 = $registry->get('U32');
$encoded = $u32->encode(42);
$hex = $encoded->toHex();

echo "v1.x: \$codec->encode(42, 'U32')\n";
echo "v2.0: \$u32->encode(42)->toHex()\n";
echo "Result: $hex\n\n";

// ============================================================
// DECODING COMPARISON
// ============================================================

echo "=== Decoding Comparison ===\n\n";

// v1.x Code:
// ---------------------------------------------------------
// $codec = new Codec();
// $value = $codec->decode('0x2a000000', 'U32');
// echo $value; // 42
// ---------------------------------------------------------

// v2.0 Code:
$bytes = ScaleBytes::fromHex('0x2a000000');
$value = $u32->decode($bytes);

echo "v1.x: \$codec->decode('0x2a000000', 'U32')\n";
echo "v2.0: \$u32->decode(ScaleBytes::fromHex('0x2a000000'))\n";
echo "Result: $value\n\n";

// ============================================================
// COMPACT ENCODING COMPARISON
// ============================================================

echo "=== Compact Encoding Comparison ===\n\n";

// v1.x Code:
// ---------------------------------------------------------
// $codec = new Codec();
// $hex = $codec->encodeCompact(1000);
// ---------------------------------------------------------

// v2.0 Code:
$compact = $registry->get('Compact');
$encoded = $compact->encode(1000);

echo "v1.x: \$codec->encodeCompact(1000)\n";
echo "v2.0: \$compact->encode(1000)->toHex()\n";
echo "Result: {$encoded->toHex()}\n\n";

// ============================================================
// VECTOR ENCODING COMPARISON
// ============================================================

echo "=== Vector Encoding Comparison ===\n\n";

use Substrate\ScaleCodec\Types\VecType;

// v1.x Code:
// ---------------------------------------------------------
// $codec = new Codec();
// $hex = $codec->encode([1, 2, 3], 'Vec<U8>');
// ---------------------------------------------------------

// v2.0 Code:
$vecU8 = (new VecType($registry))->setElementType($registry->get('U8'));
$encoded = $vecU8->encode([1, 2, 3]);

echo "v1.x: \$codec->encode([1,2,3], 'Vec<U8>')\n";
echo "v2.0: \$vecU8->encode([1,2,3])->toHex()\n";
echo "Result: {$encoded->toHex()}\n\n";

// ============================================================
// OPTION ENCODING COMPARISON
// ============================================================

echo "=== Option Encoding Comparison ===\n\n";

use Substrate\ScaleCodec\Types\OptionType;

// v1.x Code:
// ---------------------------------------------------------
// $codec = new Codec();
// $hexNone = $codec->encode(null, 'Option<U32>');
// $hexSome = $codec->encode(42, 'Option<U32>');
// ---------------------------------------------------------

// v2.0 Code:
$optionU32 = (new OptionType($registry))->setInnerType($registry->get('U32'));
$encodedNone = $optionU32->encode(null);
$encodedSome = $optionU32->encode(42);

echo "v1.x: \$codec->encode(null, 'Option<U32>')\n";
echo "v2.0: \$optionU32->encode(null)->toHex()\n";
echo "Result (None): {$encodedNone->toHex()}\n";
echo "Result (Some): {$encodedSome->toHex()}\n\n";

// ============================================================
// LARGE INTEGER HANDLING
// ============================================================

echo "=== Large Integer Handling ===\n\n";

// v1.x: Could lose precision for large integers
// ---------------------------------------------------------
// $u64 = new U64();
// $encoded = $u64->encode(18446744073709551615); // May overflow
// ---------------------------------------------------------

// v2.0: Use string for large integers
$u64 = $registry->get('U64');
$encoded = $u64->encode('18446744073709551615');

echo "v1.x: \$u64->encode(18446744073709551615) // May lose precision\n";
echo "v2.0: \$u64->encode('18446744073709551615') // String input\n";
echo "Result: {$encoded->toHex()}\n\n";

// ============================================================
// ERROR HANDLING
// ============================================================

echo "=== Error Handling ===\n\n";

// v1.x: Basic exception handling
// ---------------------------------------------------------
// try {
//     $codec->encode($value, $type);
// } catch (\Exception $e) {
//     echo $e->getMessage();
// }
// ---------------------------------------------------------

// v2.0: Specific exception types
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;
use Substrate\ScaleCodec\Exception\InvalidTypeException;

echo "v1.x: catch (\\Exception \$e)\n";
echo "v2.0: catch (ScaleEncodeException|ScaleDecodeException|InvalidTypeException \$e)\n\n";

// ============================================================
// COMPATIBILITY WRAPPER
// ============================================================

echo "=== Compatibility Wrapper Example ===\n\n";

/**
 * Compatibility wrapper for gradual migration
 * Helps maintain v1.x style API while using v2.0 internally
 */
class LegacyCodecWrapper
{
    private TypeRegistry $registry;
    
    public function __construct()
    {
        $this->registry = new TypeRegistry();
    }
    
    public function encode($value, string $type): string
    {
        return $this->registry->get($type)->encode($value)->toHex();
    }
    
    public function decode(string $hex, string $type): mixed
    {
        return $this->registry->get($type)->decode(ScaleBytes::fromHex($hex));
    }
    
    public function encodeCompact($value): string
    {
        return $this->registry->get('Compact')->encode($value)->toHex();
    }
}

// Usage example:
$wrapper = new LegacyCodecWrapper();
$hex = $wrapper->encode(42, 'U32');
echo "Wrapper encode(42, 'U32'): $hex\n";

$value = $wrapper->decode($hex, 'U32');
echo "Wrapper decode('$hex', 'U32'): $value\n";

echo "\n=== Migration Examples Complete ===\n";
