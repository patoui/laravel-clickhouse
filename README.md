# Laravel Clickhouse

[![Latest Version on Packagist](https://img.shields.io/packagist/v/patoui/laravel-clickhouse.svg?style=flat-square)](https://packagist.org/packages/patoui/laravel-clickhouse)
[![Build Status](https://img.shields.io/travis/patoui/laravel-clickhouse/master.svg?style=flat-square)](https://travis-ci.org/patoui/laravel-clickhouse)
[![Quality Score](https://img.shields.io/scrutinizer/g/patoui/laravel-clickhouse.svg?style=flat-square)](https://scrutinizer-ci.com/g/patoui/laravel-clickhouse)
[![Total Downloads](https://img.shields.io/packagist/dt/patoui/laravel-clickhouse.svg?style=flat-square)](https://packagist.org/packages/patoui/laravel-clickhouse)

An Eloquent model and Query builder with support for Clickhouse using the [SeasClick](https://github.com/seasx/seasclick) extension.

## ⚠️ WARNING ⚠️

**This package/repository is in active development, use at your own risk.**

## Installation

You can install the package via composer:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/patoui/laravel-clickhouse"
        }
    ],
    "require": {
        "patoui/laravel-clickhouse": "dev-master"
    }
}
```

## Usage

``` php
'providers' => [
    // Other Service Providers
    Patoui\LaravelClickhouse\LaravelClickhouseServiceProvider::class,
],
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email patrique.ouimet@gmail.com instead of using the issue tracker.

## Credits

- [Patrique Ouimet](https://github.com/patoui)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
