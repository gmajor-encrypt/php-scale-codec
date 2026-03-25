# PHP SCALE Codec Benchmark Guide

This directory contains performance benchmarks for the SCALE Codec library using PHPBench.

## Prerequisites

Install dependencies:
```bash
composer install
```

## Running Benchmarks

### Quick Benchmark (for development)
```bash
composer bench:quick
```

### Standard Benchmark
```bash
composer bench
```

### Full Benchmark (for CI/release)
```bash
composer bench:full
```

## Benchmark Categories

### IntegerBench
- U8, U16, U32, U64, U128 encoding/decoding
- Measures primitive integer performance

### CompactBench
- Compact integer encoding/decoding
- Tests different value ranges (small, medium, large, big integer)

### CompoundBench
- Vec<T> encoding/decoding
- Option<T> encoding/decoding
- Tuple encoding/decoding

### SpecialBench
- Bytes encoding/decoding
- String encoding/decoding
- AccountId encoding/decoding

### MetadataBench
- Metadata parsing performance
- Cached vs uncached parsing

### ExtrinsicBench
- Extrinsic building performance
- Extrinsic encoding/decoding

### EventBench
- Event parsing performance
- Event indexing performance

## Performance Targets

| Category | Target (μs) | Notes |
|----------|-------------|-------|
| U8 encode | < 1 | Single byte |
| U32 encode | < 1 | 4 bytes |
| Compact encode (small) | < 1 | Values 0-63 |
| Vec<U8> encode (10 items) | < 10 | 10 elements |
| Option encode | < 2 | Single value |

## Interpreting Results

- **Mean**: Average time across all iterations
- **Min**: Best case performance
- **Max**: Worst case performance
- **RPS**: Operations per second

Lower values are better. Compare results between versions to detect regressions.

## Adding New Benchmarks

1. Create a new `*Bench.php` file in this directory
2. Extend the class with `@BeforeMethods`, `@Iterations`, `@Revs`, `@Warmup` annotations
3. Name benchmark methods with `bench` prefix
4. Add the file to version control
