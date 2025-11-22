<?php

declare(strict_types=1);

namespace Diffhead\PHP\LaravelDataEnrichment\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Diffhead\PHP\LaravelDataEnrichment\Manager\HttpManager useHeader(\BackedEnum $header)
 * @method static \Diffhead\PHP\LaravelDataEnrichment\Manager\HttpManager cleanRequests()
 * @method static \Diffhead\PHP\DataEnrichmentKit\Builder addRequest(string $target, string $field, array<int,array{key:string,alias:string}|\Diffhead\PHP\DataEnrichmentKit\Object\Item> $items)
 * @method static \Psr\Http\Message\MessageInterface setRequests(\Psr\Http\Message\MessageInterface $message)
 * @method static \Psr\Http\Message\MessageInterface enrichMessage(\Psr\Http\Message\MessageInterface $message)
 */
class HttpEnrichment extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return \Diffhead\PHP\LaravelDataEnrichment\Manager\HttpManager::class;
    }
}
