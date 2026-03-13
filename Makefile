.PHONY: sniff test coverage

sniff: vendor/autoload.php ## Detects code style issues with phpcs
	vendor/bin/phpcs --standard=PSR2 src -n

coverage: vendor/autoload.php
	XDEBUG_MODE=coverage vendor/bin/phpunit --verbose --coverage-text

test: vendor/autoload.php
	vendor/bin/phpunit --verbose

vendor/autoload.php:
	composer install --no-interaction --prefer-dist