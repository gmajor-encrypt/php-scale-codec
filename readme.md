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

$generator = Base::create();

$scaleBytes = new ScaleBytes("64");
$codec = $generator->U8($scaleBytes);
echo $codec;
```


### Encode

```php
<?php
use Codec\Base;
$generator = Base::create();

$encode = $generator->U8();
echo $encode->encode(100);

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