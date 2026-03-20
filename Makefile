.PHONY: sniff test coverage stan cs

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

vendor/autoload.php:
	composer install --no-interaction --prefer-dist
