<?php

declare(strict_types=1);

namespace Diffhead\PHP\LaravelDataEnrichment\Manager;

class ArrayManager extends AbstractManager
{
    public function enrichData(array $data): array
    {
        return $this->enricher->enrich($data, $this->getRequests());
    }
}
