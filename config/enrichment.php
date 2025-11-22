<?php

return [
    /**
     * Data enrichment requests parser
     */
    'parser' => \Diffhead\PHP\DataEnrichmentKit\Service\Parser::class,
    /**
     * Data enrichment requests serializer
     */
    'serializer' => \Diffhead\PHP\DataEnrichmentKit\Service\Serializer::class,
    /**
     * Enrichment business logic
     */
    'enrichment' => \Diffhead\PHP\DataEnrichmentKit\Service\Enrichment::class,
    /**
     * DI container bindings if you want to register specific implementations
     * automatically.
     */
    'bindings' => [
        /**
         * \App\Repository\Repository\UserRepositoryInterface::class =>
         * \App\Repository\Repository\UserRepository::class,
         */
    ],
    /**
     * Repositories mapping where key is the target name which will be passed
     * inside the request and value is the repository class name.
     *
     * The repository class will be resolved via DI container and it should
     * implement \Diffhead\PHP\DataEnrichmentKit\Interface\Repository interface.
     */
    'repositories' => [
        /**
         * 'user' => \App\Repository\Repository\UserRepository::class,
         */
    ],
];
