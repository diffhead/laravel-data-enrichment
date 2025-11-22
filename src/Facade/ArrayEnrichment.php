<?php

declare(strict_types=1);

namespace Diffhead\PHP\LaravelDataEnrichment\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Diffhead\PHP\LaravelDataEnrichment\Manager\ArrayManager cleanRequests()
 * @method static \Diffhead\PHP\DataEnrichmentKit\Builder addRequest(string $target, string $field, array<int,array{key:string,alias:string}|\Diffhead\PHP\DataEnrichmentKit\Object\Item> $items)
 * @method static array enrichData(array $data)
 */
class ArrayEnrichment extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return \Diffhead\PHP\LaravelDataEnrichment\Manager\ArrayManager::class;
    }
}
