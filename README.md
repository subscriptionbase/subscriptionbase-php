# SubscriptionBase for PHPs

PHP 5.6 and later.

> Even if it is not a corresponding version, it may work, but it does not support it.
  Due to the PHP [END OF LIFE](http://php.net/supported-versions.php) cycle.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Add this to your `composer.json`:

    {
      "require": {
        "subscriptionbase/subscriptionbase-php": "~1.0"
      }
    }

Then install via:

    composer install

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

    require_once 'vendor/autoload.php';

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/subscriptionbase/subscriptionbase-php/releases). Then, to use the bindings, include the `init.php` file.

    require_once '/path/to/subscriptionbase-php/init.php';

## Documentation

- Please see our official [api-reference](https://subscriptionbase.io/api-reference).

## Tests

In order to run tests first install [PHPUnit](http://packagist.org/packages/phpunit/phpunit) via [Composer](http://getcomposer.org/):

    composer update --dev

To run the test suite:

    ./vendor/bin/phpunit
