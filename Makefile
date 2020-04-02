vendor/autoload.php:
	composer install --no-interaction --prefer-dist

.PHONY: test
test: vendor/autoload.php
	vendor/bin/phpunit --verbose