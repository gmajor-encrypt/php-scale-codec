<?php

/**
 * Example: Basic SCALE Encoding and Decoding
 * 
 * This example demonstrates the basic usage of SCALE codec
 * for encoding and decoding primitive types.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

echo "=== SCALE Codec Basic Examples ===\n\n";

// Create type registry
$registry = new TypeRegistry();

// ============================================
// Unsigned Integers
// ============================================
echo "--- Unsigned Integers ---\n\n";

// U8 (0-255)
$u8 = $registry->get('U8');
echo "U8 encode(255): " . $u8->encode(255)->toHex() . "\n";
echo "U8 encode(0): " . $u8->encode(0)->toHex() . "\n";

// U32 (32-bit unsigned)
$u32 = $registry->get('U32');
echo "U32 encode(12345): " . $u32->encode(12345)->toHex() . "\n";

// U64 (64-bit unsigned) - Note: returns string for large values
$u64 = $registry->get('U64');
echo "U64 encode('18446744073709551615'): " . $u64->encode('18446744073709551615')->toHex() . "\n";

// ============================================
// Signed Integers
// ============================================
echo "\n--- Signed Integers ---\n\n";

$i8 = $registry->get('I8');
echo "I8 encode(-1): " . $i8->encode(-1)->toHex() . "\n";
echo "I8 encode(-128): " . $i8->encode(-128)->toHex() . "\n";
echo "I8 encode(127): " . $i8->encode(127)->toHex() . "\n";

$i32 = $registry->get('I32');
echo "I32 encode(-1): " . $i32->encode(-1)->toHex() . "\n";

// ============================================
// Boolean
// ============================================
echo "\n--- Boolean ---\n\n";

$bool = $registry->get('Bool');
echo "Bool encode(true): " . $bool->encode(true)->toHex() . "\n";
echo "Bool encode(false): " . $bool->encode(false)->toHex() . "\n";

// ============================================
// String/Text
// ============================================
echo "\n--- String ---\n\n";

$string = $registry->get('String');
echo "String encode('Hello'): " . $string->encode('Hello')->toHex() . "\n";
echo "String encode('区块链'): " . $string->encode('区块链')->toHex() . "\n";

// ============================================
// Compact Integers
// ============================================
echo "\n--- Compact Integers ---\n\n";

$compact = $registry->get('Compact');
echo "Compact encode(0): " . $compact->encode(0)->toHex() . " (1 byte)\n";
echo "Compact encode(42): " . $compact->encode(42)->toHex() . " (1 byte)\n";
echo "Compact encode(63): " . $compact->encode(63)->toHex() . " (1 byte, max)\n";
echo "Compact encode(64): " . $compact->encode(64)->toHex() . " (2 bytes)\n";
echo "Compact encode(16383): " . $compact->encode(16383)->toHex() . " (2 bytes, max)\n";
echo "Compact encode(16384): " . $compact->encode(16384)->toHex() . " (4 bytes)\n";

// ============================================
// Decoding Examples
// ============================================
echo "\n--- Decoding Examples ---\n\n";

// Decode U32
$bytes = ScaleBytes::fromHex('0x39300000');
$decoded = $u32->decode($bytes);
echo "U32 decode('0x39300000'): " . $decoded . "\n";

// Decode String
$bytes = ScaleBytes::fromHex('0x1448656c6c6f');
$decoded = $string->decode($bytes);
echo "String decode('0x1448656c6c6f'): " . $decoded . "\n";

// Decode Compact
$bytes = ScaleBytes::fromHex('0xa8');
$decoded = $compact->decode($bytes);
echo "Compact decode('0xa8'): " . $decoded . "\n";

echo "\n=== Examples Complete ===\n";
