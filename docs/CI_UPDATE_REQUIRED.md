# Required CI Update for Issue #43

To complete Issue #43, the following CI workflow changes are needed:

## Add to `.github/workflows/ci.yml`

```yaml
  static-analysis:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: gmp

      - name: Get composer cache directory
        id: composer-cache
        run: echo "::set-output name=dir::$(composer config cache-files-dir)"

      - name: Cache dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: PHPStan
        run: make stan

  code-style:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: gmp

      - name: Get composer cache directory
        id: composer-cache
        run: echo "::set-output name=dir::$(composer config cache-files-dir)"

      - name: Cache dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Code Style (PHPCS)
        run: make sniff
```

## Current Makefile Commands

- `make stan` - Run PHPStan level 9
- `make sniff` - Check code style (PSR-12)
- `make cs` - Fix code style

## PHPStan Configuration

Already configured in `phpstan.neon`:
```yaml
parameters:
    level: 9
    paths:
        - src
        - tests
```
