# Static Analysis and Code Quality

This project uses PHPStan for static analysis and PHPCS for code style checking.

## PHPStan (Static Analysis)

We use PHPStan level 9 for strict type checking.

### Run Locally

```bash
make stan
```

### Configuration

See `phpstan.neon` for configuration.

## Code Style (PHPCS)

We follow PSR-12 coding standards.

### Check Code Style

```bash
make sniff
```

### Fix Code Style

```bash
make cs
```

## CI Integration

The CI pipeline runs:
1. Unit tests (PHPUnit)
2. Static analysis (PHPStan)
3. Code style check (PHPCS)

All checks must pass before merging.
