# Laravel Clickhouse

[![Latest Version on Packagist](https://img.shields.io/packagist/v/patoui/laravel-clickhouse.svg?style=flat-square)](https://packagist.org/packages/patoui/laravel-clickhouse)
[![Build Status](https://img.shields.io/travis/patoui/laravel-clickhouse/master.svg?style=flat-square)](https://travis-ci.org/patoui/laravel-clickhouse)
[![Quality Score](https://img.shields.io/scrutinizer/g/patoui/laravel-clickhouse.svg?style=flat-square)](https://scrutinizer-ci.com/g/patoui/laravel-clickhouse)
[![Total Downloads](https://img.shields.io/packagist/dt/patoui/laravel-clickhouse.svg?style=flat-square)](https://packagist.org/packages/patoui/laravel-clickhouse)

An Eloquent model and Query builder with support for Clickhouse using the [SeasClick](https://github.com/seasx/seasclick) extension.

## ⚠️ WARNING ⚠️

**This package/repository is in active development, use at your own risk.**

## Requirements

[SeasClick](https://github.com/seasx/seasclick) extension 

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

Add service provider

``` php
'providers' => [
    // Other Service Providers
    Patoui\LaravelClickhouse\LaravelClickhouseServiceProvider::class,
],
```

Add connection details

```php
'connections' => [
    'clickhouse' => [
        'host'     => '127.0.0.1',
        'port'     => '9000',
        'username' => 'default',
        'password' => '',
    ]
],
```

## Usage

Use as you normally would an eloquent model or query builder.

See `tests` directory for additional examples

Below is a list of currently untested methods or functionality. This does not mean that they won't work, just that there is currently no test coverage.

- `union`
- `orWhere`
- JSON where clause `->where('meta->name', 'foobar')`
- `whereJsonContains`
- `whereJsonLength`
- `whereBetween`
- `whereNotBetween`
- `whereIn` or `whereNotIn`
- `whereNull` or `whereNotNull`
- `whereDate`, `whereMonth`, `whereDay`, `whereYear`, `whereTime`
- `orWhereColumn`
- `whereExists`
- Subquery Where Clauses
- Upserts
- `updateOrInsert`
- Updating JSON Columns `->update(['options->enabled' => true]);`
- `increment` or `decrement`

```php
DB::connection('clickhouse')->insert(
    'analytics',
    ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
);
        
DB::connection('clickhouse')->table('analytics')->insert([
    'ts'          => time(),
    'analytic_id' => 321,
    'status'      => 204,
]);

DB::connection('clickhouse')
    ->table('analytics')
    ->where('ts', '>', strtotime('-1 day'))
    ->count();
    
class Analytic extends ClickhouseModel
{
    public $guarded = []; // optional, added for brevity
}

Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => 204, 'name' => 'page_view']);

Analytic::where('ts', '>', strtotime('-1 day'))->count();

Analytic::where('name', 'page_view')->update(['name' => 'page_visit']);
```

### Testing

Testing is done within docker to simplify setting up Clickhouse

```bash
docker-compose up --build
```

Run the tests:

```bash
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
