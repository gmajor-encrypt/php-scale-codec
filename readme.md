![grants_badge](./grants_badge.png)

### Substrate scale codec

[![Travis CI](https://api.travis-ci.org/gmajor-encrypt/php-scale-codec.svg)](https://travis-ci.org/github/gmajor-encrypt/php-scale-codec)

---
PHP SCALE Codec For substrate


## Installation

```sh
composer require gmajor/substrate-codec-php
```

## Basic Usage

### Autoloading

Codec supports `PSR-4` autoloaders.

```php
<?php
# When installed via composer
require_once 'vendor/autoload.php';
```


### Decode

```php
<?php
use Codec\ScaleBytes;
use Codec\Base;
use Codec\Types\ScaleInstance;

$codec = new ScaleInstance(Base::create());
// Uint Support U8,U16,U32,U64,U128
$codec->process("U8", new ScaleBytes("64"));
$codec->process("U16", new ScaleBytes("0300"));
$codec->process("U32", new ScaleBytes("64000000"));
$codec->process("U64", new ScaleBytes("471b47acc5a70000"));
$codec->process("U128", new ScaleBytes("e52d2254c67c430a0000000000000000"));
// Compact Support Compact int or other Mixed type, like Compact<Balance>
$codec->process("Compact<u32>", new ScaleBytes("02093d00"));
// Address Support Address/Account Id/MultiAddress
$codec->process("Address", new ScaleBytes("ff1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318"));
// Option
$codec->process("Option<bool>", new ScaleBytes("02"));
// String 
$codec->process("String", new ScaleBytes("1054657374"));
// Bytes
$codec->process("Bytes", new ScaleBytes("08ffff"));
// Vec
$codec->process("Vec<(u32, u32, u16)>", new ScaleBytes("08cc0200000000ce0200000001"));
$codec->process("Vec<u8>", new ScaleBytes("08ffff"));
// Enum
$codec =$codec->createTypeByTypeString("Enum");
$codec->valueList = [0, 1, 49, 50];
$codec->init(new ScaleBytes("02"));
$codec->decode();
// Struct
$codec =$codec->createTypeByTypeString("Struct");
$codec->typeStruct = ["a" => "Compact<u32>", "b" => "Compact<u32>"];
$codec->init(new ScaleBytes("0c00"));
$codec->decode();
```

### Encode

```php
<?php
use Codec\Base;
use Codec\Types\ScaleInstance;

$codec = new ScaleInstance(Base::create());
// uint, encode support U8,U16,U32,U64,U128, Note that php int type support needs to be less than 9223372036854775807, if it exceeds, it needs to be changed to string type
$codec->createTypeByTypeString("U8")->encode(300);
$codec->createTypeByTypeString("U16")->encode(5000);
$codec->createTypeByTypeString("U32")->encode(100100100);
$codec->createTypeByTypeString("U64")->encode(184467440737095);
$codec->createTypeByTypeString("U128")->encode(739571955075788261);
// Compact
$codec->createTypeByTypeString("Compact")->encode(2503000000000000000);
// Address
$codec->createTypeByTypeString("Address")->encode("1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318");
// Option
$codec->createTypeByTypeString("option<Compact<u32>>")->encode(63);
// String
$codec->createTypeByTypeString("String")->encode("Test");
// Bytes
 $codec->createTypeByTypeString("Bytes")->encode("0xffff");
// Vec
$codec->createTypeByTypeString("Vec<u32>")->encode([1, 2, 3, 4]);
// Enum
$codec =$codec->createTypeByTypeString("Enum");
$codec->valueList = [0, 1, 49, 50];
$codec->encode(49);
// Struct
$codec =$codec->createTypeByTypeString("Struct");
$codec->typeStruct = ["a" => "Compact<u32>", "b" => "Compact<u32>"];
$codec->encode(["a" => 3, "b" => 0]);
```

### Example

More examples can refer to the test file https://github.com/gmajor-encrypt/php-scale-codec/blob/master/test/Codec/TypeTest.php

## Test

```
make test
```


## Resources

- [polkadot.js](http://polkadot.js.org/)
- [polkascan](https://github.com/polkascan)
- [scale.go](https://github.com/itering/scale.go)
- [substrate.dev](https://substrate.dev/docs/en/knowledgebase/advanced/codec)


## License

The package is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT)