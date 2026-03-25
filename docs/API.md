# php-scale-codec API Documentation

Version 2.0.0

## Overview

php-scale-codec is a PHP implementation of the SCALE (Simple Concatenated Aggregate Little-Endian) codec used in Substrate-based blockchains like Polkadot.

## Installation

```bash
composer require gmajor/substrate-codec-php
```

## Quick Start

```php
<?php

use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

// Create a type registry
$registry = new TypeRegistry();

// Encode a U32 value
$u32 = $registry->get('U32');
$encoded = $u32->encode(12345);
echo $encoded->toHex(); // 0x39300000

// Decode it back
$bytes = ScaleBytes::fromHex('0x39300000');
$decoded = $u32->decode($bytes);
echo $decoded; // 12345
```

## Core Components

### TypeRegistry

Central registry for all SCALE types.

```php
$registry = new TypeRegistry();

// Get primitive types
$u8 = $registry->get('U8');
$u32 = $registry->get('U32');
$bool = $registry->get('Bool');

// Get complex types
$vec = $registry->get('Vec');
$option = $registry->get('Option');
```

### ScaleBytes

Container for SCALE-encoded bytes.

```php
// Create from hex
$bytes = ScaleBytes::fromHex('0x010203');

// Create from byte array
$bytes = ScaleBytes::fromBytes([1, 2, 3]);

// Convert to hex
$hex = $bytes->toHex();

// Read bytes
$byte = $bytes->readByte();
$bytes = $bytes->readBytes(4);
```

### TypeFactory

Factory for creating parameterized types.

```php
use Substrate\ScaleCodec\Types\TypeFactory;

$factory = new TypeFactory($registry);

// Create Vec<U8>
$vecU8 = $factory->create('Vec<U8>');

// Create Option<U32>
$optionU32 = $factory->create('Option<U32>');

// Create tuple
$tuple = $factory->create('(U8, U32, U64)');
```

## Primitive Types

| Type | Description | Range |
|------|-------------|-------|
| U8 | Unsigned 8-bit | 0 - 255 |
| U16 | Unsigned 16-bit | 0 - 65,535 |
| U32 | Unsigned 32-bit | 0 - 4,294,967,295 |
| U64 | Unsigned 64-bit | 0 - 18,446,744,073,709,551,615 |
| U128 | Unsigned 128-bit | 0 - 2^128 - 1 |
| I8 | Signed 8-bit | -128 - 127 |
| I16 | Signed 16-bit | -32,768 - 32,767 |
| I32 | Signed 32-bit | -2^31 - 2^31 - 1 |
| I64 | Signed 64-bit | -2^63 - 2^63 - 1 |
| I128 | Signed 128-bit | -2^127 - 2^127 - 1 |
| Bool | Boolean | true/false |
| String | UTF-8 string | any length |

## Compound Types

### Vec<T>

Dynamically sized vector of elements.

```php
$vec = new VecType($registry);
$vec->setElementType($registry->get('U8'));

// Encode
$encoded = $vec->encode([1, 2, 3, 4, 5]);

// Decode
$decoded = $vec->decode(ScaleBytes::fromBytes($encoded->toBytes()));
```

### Option<T>

Optional value (Some or None).

```php
$option = new OptionType($registry);
$option->setInnerType($registry->get('U32'));

// Encode Some
$encoded = $option->encode(42);

// Encode None
$encoded = $option->encode(null);
```

### Struct

Fixed structure with named fields.

```php
$struct = new StructType($registry);
$struct->setFields([
    'id' => $registry->get('U32'),
    'name' => $registry->get('String'),
    'active' => $registry->get('Bool'),
]);

$encoded = $struct->encode([
    'id' => 1,
    'name' => 'Alice',
    'active' => true,
]);
```

### Enum

Enumerated type with variants.

```php
$enum = new EnumType($registry);
$enum->addVariant('None', 0);
$enum->addVariant('Some', 1, $registry->get('U32'));

$encoded = $enum->encode(['Some' => 42]);
```

## Compact Encoding

SCALE uses a compact encoding for variable-length integers.

```php
$compact = $registry->get('Compact');

// Small values (0-63): 1 byte
$compact->encode(42); // 0xa8

// Medium values (64-16383): 2 bytes
$compact->encode(100); // 0x9101

// Large values: 4+ bytes
$compact->encode(1000000);
```

## Metadata

Parse Substrate metadata for dynamic type resolution.

```php
use Substrate\ScaleCodec\Metadata\MetadataParser;

$parser = new MetadataParser();
$metadata = $parser->parse($metadataHex);

// Access pallets
$pallet = $metadata->getPallet('System');

// Get events
$events = $metadata->getPalletEvents('System');
```

## Extrinsic

Create and sign extrinsics.

```php
use Substrate\ScaleCodec\Extrinsic\ExtrinsicBuilder;

$builder = new ExtrinsicBuilder($registry);
$extrinsic = $builder
    ->setVersion(4)
    ->setPallet('Balances')
    ->setFunction('transfer')
    ->setArgs([
        'dest' => $accountId,
        'value' => 1000000000,
    ])
    ->sign($keypair)
    ->build();
```

## Error Handling

```php
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

try {
    $encoded = $u32->encode(4294967296); // Overflow
} catch (ScaleEncodeException $e) {
    echo "Encoding error: " . $e->getMessage();
}

try {
    $decoded = $u32->decode($insufficientBytes);
} catch (ScaleDecodeException $e) {
    echo "Decoding error: " . $e->getMessage();
}
```

## Versioning

- Supports Substrate Metadata v12-v15
- SCALE codec specification compliant
- polkadot.js compatible

## See Also

- [SCALE Codec Specification](https://docs.substrate.io/reference/scale-codec/)
- [Substrate Documentation](https://docs.substrate.io/)
- [polkadot.js API](https://polkadot.js.org/docs/api/)
