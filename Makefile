.PHONY: sniff test coverage stan cs bench bench-full bench-quick all compat docs

sniff: vendor/autoload.php ## Detects code style issues with phpcs
	vendor/bin/phpcs --standard=PSR12 src tests -n

stan: vendor/autoload.php ## Run PHPStan static analysis
	vendor/bin/phpstan analyse src tests --level=9

cs: vendor/autoload.php ## Fix code style
	vendor/bin/phpcbf --standard=PSR12 src tests

coverage: vendor/autoload.php ## Run tests with coverage
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text

test: vendor/autoload.php ## Run unit tests
	vendor/bin/phpunit

bench: vendor/autoload.php ## Run standard benchmarks
	vendor/bin/phpbench run --report=default

bench-full: vendor/autoload.php ## Run full benchmarks for CI
	vendor/bin/phpbench run --profile=full --report=default

bench-quick: vendor/autoload.php ## Run quick benchmarks
	vendor/bin/phpbench run --profile=quick --report=default

compat: vendor/autoload.php ## Run polkadot.js compatibility tests
	php compat/tests/php-compatibility-test.php

docs: ## Generate API documentation
	vendor/bin/phpdoc

all: test stan cs ## Run all checks (tests, static analysis, code style)

vendor/autoload.php:
	composer install --no-interaction --prefer-dist

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'
