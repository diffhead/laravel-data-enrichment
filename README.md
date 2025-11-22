# Laravel Data Enrichment

Laravel Data Enrichment helps you enrich and augment data across your Laravel applications. It offers a flexible, extensible workflow to collect enrichment requests and resolve them against repositories, whether you work with arrays, HTTP messages, or custom data sources.

## Features

- **Multiple sources:** Enrich array data, HTTP requests, and responses.
- **Middleware support:** Pin enrichment requests to controller responses automatically.
- **Extensible managers:** Plug in custom logic via interfaces and repositories.
- **Simple facades:** Convenient API via `ArrayEnrichment` and `HttpEnrichment`.

## Additional Info

- Core library: [diffhead/php-data-enrichment-kit](https://github.com/diffhead/php-data-enrichment-kit)
- This package provides Laravel integration (service provider, facades, middleware, and configuration) on top of the core kit.

## Installation

Install via Composer:

```bash
composer require diffhead/laravel-data-enrichment
```

Publish the configuration (if needed):

```bash
php artisan vendor:publish --provider="Diffhead\PHP\LaravelDataEnrichment\ServiceProvider"
```

## Configuration

The configuration file `config/enrichment.php` controls how enrichment works. Customize it for your application.

- **Repositories:** On the receiver side, create repositories implementing `\Diffhead\PHP\DataEnrichmentKit\Interface\Repository` and list them under `enrichment.repositories`.
- **Bindings:** Optionally map repository interfaces to implementations via `enrichment.bindings` for auto-resolution.
- **Custom logic:** Override the enrichment workflow by providing your implementations for:
    - `\Diffhead\PHP\DataEnrichmentKit\Interface\Parser` — parse raw requests
    - `\Diffhead\PHP\DataEnrichmentKit\Interface\Serializer` — serialize request objects
    - `\Diffhead\PHP\DataEnrichmentKit\Interface\Enrichment` — core enrichment business logic

## Usage

### Array Enrichment

Use the `ArrayEnrichment` facade to enrich plain PHP arrays:

```php
use Diffhead\LaravelDataEnrichment\Facade\ArrayEnrichment;

$books = [
    [
        'title' => 'Magic Things: How To',
        'author_id' => 353,
    ],
];

ArrayEnrichment::addRequest('user', 'id', [
    ['key' => '*.author_id', 'alias' => 'author'],
]);

$enrichedBooks = ArrayEnrichment::enrich($books);
/**
 * Result:
 * [
 *   [
 *     'title' => 'Magic Things: How To',
 *     'author_id' => 353,
 *     'author' => [
 *       'id' => 353,
 *       'name' => 'John Doe',
 *       'email' => 'john-doe@mysite.com'
 *     ]
 *   ],
 * ]
 */
print_r($enrichedBooks);

/**
 * When done, clear the request storage and add new
 * requests for the next data object to enrich.
 */
ArrayEnrichment::clearRequests();
```

### HTTP Enrichment

Use the `HttpEnrichment` facade to enrich HTTP requests or responses:

```php
use Diffhead\LaravelDataEnrichment\Facade\HttpEnrichment;

enum Header: string
{
    case XEnrich = 'X-Enrich';
}

$psrFactory = new PsrHttpFactory();
/**
 * @var \Symfony\Component\HttpFoundation\Response $response
 * @var \Psr\Http\Message\MessageInterface $psrMessage
 */
$psrMessage = $psrFactory->createResponse($response);

/**
 * Optionally set the header used for enrichment.
 * Default: \Diffhead\LaravelDataEnrichment\Header::XEnrichRequest
 * Accepts any BackedEnum.
 * Note: if you change the default on the client, also change it on the server.
 */
HttpEnrichment::useHeader(Header::XEnrich);

/**
 * Add enrichment requests to storage.
 */
HttpEnrichment::addRequest('user', 'id', [
    ['key' => 'data.*.assigned.*.user_id', 'alias' => 'user'],
    ['key' => 'data.*.created_by', 'alias' => 'creator'],
]);

/**
 * Client-side step: attach requests before passing the message downstream.
 */
$psrMessagePrepared = HttpEnrichment::setRequests($psrMessage);

/**
 * Example (gateway side): enrich a response.
 * This method parses and enriches the JSON payload in the PSR message.
 * Note: call HttpEnrichment::useHeader again if you changed it earlier.
 */
$psrMessageEnriched = HttpEnrichment::enrichMessage($psrMessagePrepared);
```

### Middleware

Use the `enrichment.pin-requests` middleware to automatically pin added requests to the controller response:

```php
use Diffhead\LaravelDataEnrichment\Facade\HttpEnrichment;

Route::middleware(['enrichment.pin-requests'])->group(
    function () {
        Route::get('/posts', function () {
            HttpEnrichment::addRequest('user', 'id', [
                ['key' => 'data.*.owner_id', 'alias' => 'owner'],
            ]);

            return response()->json([
                'data' => [
                    ['id' => 1, 'title' => 'Cats Dev', 'owner_id' => 1],
                    ['id' => 2, 'title' => "Sponge Bob's Bio", 'owner_id' => 2],
                ],
            ]);
        });
    }
);
```

## Repository Examples

Below is an example of a repository interface and its Laravel Eloquent implementation used for enrichment. The repository searches users by a field with multiple values and returns an iterable cursor.

#### Interface

```php
namespace App\Shared\Service\User;

use Diffhead\PHP\DataEnrichmentKit\Interface\Repository;

interface SearchByFieldValuesInterface extends Repository
{
    /**
     * @param string $field
     * @param array $values
     *
     * @return iterable<int,\App\Models\User\User>
     */
    public function getByFieldValues(string $field, array $values): iterable;
}
```
#### Implementation

```php
namespace App\Shared\Service\User;

use App\Models\User\User;

class SearchByFieldValues implements SearchByFieldValuesInterface
{
    /**
     * @param string $field
     * @param array<int,mixed> $values
     *
     * @return iterable<int,\App\Models\User\User>
     */
    public function getByFieldValues(string $field, array $values): iterable
    {
        return User::query()->whereIn($field, $values)->cursor();
    }
}
```

#### Registration

`config/enrichment.php`

```php

return [
    /** ... */
    'bindings' => [
        \App\Shared\Service\User\SearchByFieldValuesInterface::class =>
        \App\Shared\Service\User\SearchByFieldValues::class
    ],
    'repositories' => [
        'user' => \App\Shared\Service\User\SearchByFieldValuesInterface::class
    ],
    /** ... */
];
```
