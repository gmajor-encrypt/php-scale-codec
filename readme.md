# php-scale-codec v2.0

PHP SCALE Codec for Substrate - Version 2.0

## Architecture Overview

### Directory Structure

```
src/
├── Bytes/          # Byte manipulation utilities
│   └── ScaleBytes.php
├── Encoder/        # Encoder interfaces
│   └── EncoderInterface.php
├── Decoder/        # Decoder interfaces
│   └── DecoderInterface.php
├── Types/          # Type definitions and implementations
│   ├── ScaleType.php      # PHP 8.2 enum for SCALE types
│   ├── TypeInterface.php  # Core type interface
│   ├── AbstractType.php   # Base type implementation
│   ├── TypeRegistry.php  # Type registration and lookup
│   ├── TypeFactory.php    # Type creation factory
│   ├── BoolType.php
│   └── NullType.php
├── Metadata/       # Metadata parsing (Phase 3)
├── Extrinsic/      # Extrinsic handling (Phase 3)
└── Exception/      # Exception classes
    ├── ScaleEncodeException.php
    ├── ScaleDecodeException.php
    └── InvalidTypeException.php

tests/
├── Bytes/
│   └── ScaleBytesTest.php
└── Types/
    ├── ScaleTypeTest.php
    └── TypeRegistryTest.php
```

## Key Components

### ScaleType Enum (PHP 8.2)

```php
use Substrate\ScaleCodec\Types\ScaleType;

$type = ScaleType::U32;
$type->getByteSize(); // 4
$type->isUnsignedInt(); // true
```

### TypeRegistry

```php
use Substrate\ScaleCodec\Types\{TypeRegistry, BoolType};

$registry = new TypeRegistry();
$registry->register('bool', new BoolType($registry));
$boolType = $registry->get('bool');
```

### ScaleBytes

```php
use Substrate\ScaleCodec\Bytes\ScaleBytes;

$bytes = ScaleBytes::fromHex('0x01020304');
$bytes->readBytes(2); // [1, 2]
$bytes->remaining(); // 2
$bytes->toHex(); // '0x01020304'
```

## Installation

```bash
composer require gmajor/substrate-codec-php
```

## Requirements

- PHP 8.2+
- ext-gmp
- ext-json
- ext-sodium

## License

MIT License
