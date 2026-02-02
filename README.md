[contributors-shield]: https://img.shields.io/github/contributors/jobmetric/laravel-location.svg?style=for-the-badge
[contributors-url]: https://github.com/jobmetric/laravel-location/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/jobmetric/laravel-location.svg?style=for-the-badge&label=Fork
[forks-url]: https://github.com/jobmetric/laravel-location/network/members
[stars-shield]: https://img.shields.io/github/stars/jobmetric/laravel-location.svg?style=for-the-badge
[stars-url]: https://github.com/jobmetric/laravel-location/stargazers
[license-shield]: https://img.shields.io/github/license/jobmetric/laravel-location.svg?style=for-the-badge
[license-url]: https://github.com/jobmetric/laravel-location/blob/master/LICENCE.md
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-blue.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/majidmohammadian

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

# Laravel Location

**Location Management for Laravel. Structured. Scalable.**

Laravel Location helps you model and manage geographic data in a clean, consistent way—from Countries and Provinces to Cities, Districts, Locations, Geo Areas and Addresses. It is designed to be used as a reusable package in real-world Laravel applications where location data needs to be normalized and shared across multiple models.

## Why Laravel Location?

### A clean hierarchy: Country → Province → City → District

Keep your geographical data normalized and queryable with a clear relational hierarchy. This makes reporting, filtering, and validation much easier across your application.

### Reusable Locations, Geo Areas and Addresses

- **Locations** are stored as unique records (country/province/city/district combination).
- **Geo Areas** can reference multiple locations (non-duplicated).
- **Addresses** can be attached to any model (polymorphic) and are stored with a location relation.

### Service-first API + Facades

Each entity is managed through a dedicated service and convenient Facades:

- `Country`, `Province`, `City`, `District`
- `Location`, `GeoArea`, `Address`

This keeps controllers thin and makes the package easy to integrate and test.

## Quick Start

Install via Composer:

```bash
composer require jobmetric/laravel-location
```

Run migrations:

```bash
php artisan migrate
```

Optionally publish config/translations (if you need to override defaults):

```bash
php artisan vendor:publish --provider="JobMetric\\Location\\LocationServiceProvider"
```

## Usage (Examples)

Store a country using the Facade:

```php
use JobMetric\Location\Facades\Country;

$response = Country::store([
    'name' => 'Iran',
    'flag' => 'iran.svg',
    'mobile_prefix' => 98,
    'validation' => [
        '/^9\\d{9}$/',
    ],
    'address_on_letter' => "{country}, {province}, {city}\n{district}, {street}, {number}",
    'status' => true,
]);
```

Attach address/geo areas to your models using traits:

```php
use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\HasAddress;
use JobMetric\Location\HasGeoArea;

class User extends Model
{
    use HasAddress;
}

class Shipping extends Model
{
    use HasGeoArea;
}
```

## Documentation

Documentation for Laravel Location is available here:

**[📚 Read Full Documentation →](https://jobmetric.github.io/packages/laravel-location/)**

The documentation includes:

- **Getting Started** - Installation and configuration
- **Traits** - `HasAddress`, `HasGeoArea`, `HasLocation`
- **Services & Facades** - Complete API reference
- **Requests & Resources** - Validation and API responses
- **Events** - Hook into lifecycle events
- **Testing** - How to run package tests and expected patterns

## Contributing

Thank you for participating in `laravel-location`. A contribution guide can be found [here](CONTRIBUTING.md).

## License

The `laravel-location` is open-sourced software licensed under the MIT license. See [License File](LICENCE.md) for more information.
