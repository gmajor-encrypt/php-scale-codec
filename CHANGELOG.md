# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-03-30

### Added

- **Phase 1: Architecture Refactoring**
  - Complete directory structure reorganization
  - New core interfaces for better extensibility
  - Improved separation of concerns

- **Phase 2: Type System**
  - Basic integer types implementation (U8-U128, I8-I128)
  - Compact type encoding/decoding
  - Special types implementation (Bool, String, Bytes)
  - Compound types (Vec, Option, Tuple, Struct, Enum)
  - Custom type registration mechanism

- **Phase 3: Metadata Parser**
  - Refactored metadata parser for better maintainability
  - Metadata v12-v15 support

- **Phase 4: Quality & Testing**
  - Static analysis integration (PHPStan level 9)
  - Unit test coverage improvement (344 tests, 654 assertions)
  - Performance benchmark system

- **Phase 5: Documentation & Compatibility**
  - API documentation generation (phpDocumentor)
  - Usage guide and examples
  - polkadot.js compatibility testing framework

- **Event Parser Optimization**
- **Extrinsic Codec Refactoring**

### Changed

- **BREAKING**: Complete architecture refactoring
- **BREAKING**: Namespace changes and class reorganization
- Minimum PHP version bumped to 8.2+

### Requirements

- PHP 8.2+
- ext-gmp
- ext-json
- ext-sodium

## [1.1.2] - Previous Release

- Metadata v14 support
- Bug fixes and stability improvements

## [1.1.0] - Previous Release

- Metadata v13 support

## [1.0.0] - Initial Release

- Basic SCALE codec implementation
- Primitive types support
- Initial metadata support
