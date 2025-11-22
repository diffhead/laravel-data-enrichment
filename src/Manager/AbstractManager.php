<?php

declare(strict_types=1);

namespace Diffhead\PHP\LaravelDataEnrichment\Manager;

use Diffhead\PHP\DataEnrichmentKit\Builder;
use Diffhead\PHP\DataEnrichmentKit\Enricher;
use Diffhead\PHP\DataEnrichmentKit\Object\Item;
use Diffhead\PHP\DataEnrichmentKit\Storage\Requests;
use InvalidArgumentException;

abstract class AbstractManager
{
    /**
     * @var array<int,\Diffhead\PHP\DataEnrichmentKit\Builder>
     */
    protected array $builders = [];

    public function __construct(
        protected Enricher $enricher,
    ) {}

    /**
     * @param string $target
     * @param string $field
     * @param array<int,array{key:string,alias:string}|\Diffhead\PHP\DataEnrichmentKit\Object\Item> $items
     *
     * @return \Diffhead\PHP\DataEnrichmentKit\Builder
     */
    public function addRequest(string $target, string $field = 'id', array $items = []): Builder
    {
        $builder = Builder::withTarget($target, $field);

        foreach ($items as $item) {
            if (is_array($item)) {
                $this->validateRequestItemAsArray($item);
            } else {
                $item = $this->objectRequestItemToArray($item);
            }

            $builder->item($item['key'], $item['alias']);
        }

        $this->builders[] = $builder;

        return $builder;
    }

    public function cleanRequests(): static
    {
        $this->builders = [];

        return $this;
    }

    protected function getRequests(): Requests
    {
        $requests = new Requests();

        foreach ($this->builders as $builder) {
            $requests->append($builder->build());
        }

        return $requests;
    }

    /**
     * @param array $item
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function validateRequestItemAsArray(array $item): void
    {
        $notExistingKey = ! array_key_exists('key', $item);
        $notExistingAlias = ! array_key_exists('alias', $item);

        if ($notExistingKey || $notExistingAlias) {
            throw new InvalidArgumentException(
                'Item as array should contain "key" and "alias" values.'
            );
        }
    }

    /**
     * @param \Diffhead\PHP\DataEnrichmentKit\Object\Item $item
     *
     * @return array{key:string,alias:string}
     */
    private function objectRequestItemToArray(Item $item): array
    {
        return [
            'key' => $item->key(),
            'alias' => $item->alias(),
        ];
    }
}
