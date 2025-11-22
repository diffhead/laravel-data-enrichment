<?php

declare(strict_types=1);

namespace Diffhead\PHP\LaravelDataEnrichment;

use Diffhead\PHP\DataEnrichmentKit\Enricher;
use Diffhead\PHP\DataEnrichmentKit\Interface\Enrichment as EnrichmentInterface;
use Diffhead\PHP\DataEnrichmentKit\Interface\Parser as ParserInterface;
use Diffhead\PHP\DataEnrichmentKit\Interface\Serializer as SerializerInterface;
use Diffhead\PHP\DataEnrichmentKit\Message;
use Diffhead\PHP\DataEnrichmentKit\Service\Enrichment;
use Diffhead\PHP\DataEnrichmentKit\Service\Parser;
use Diffhead\PHP\DataEnrichmentKit\Service\Serializer;
use Diffhead\PHP\DataEnrichmentKit\Storage\Repositories;
use Diffhead\PHP\LaravelDataEnrichment\Manager\ArrayManager;
use Diffhead\PHP\LaravelDataEnrichment\Manager\HttpManager;
use Diffhead\PHP\LaravelDataEnrichment\Middleware\PinRequestsToResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    private array $middlewares = [
        'enrichment.pin-requests' => PinRequestsToResponse::class,
    ];

    public function register(): void
    {
        $this->registerBindings();
        $this->registerServices();
        $this->registerRepositories();
        $this->registerFacadeManagers();
    }

    public function boot(Router $router): void
    {
        $this->registerConfigsPublishment();

        foreach ($this->middlewares as $alias => $class) {
            $router->aliasMiddleware($alias, $class);
        }
    }

    private function registerBindings(): void
    {
        /**
         * @var array<class-string,class-string> $bindings
         */
        $bindings = config('enrichment.bindings', []);

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    private function registerServices(): void
    {
        $this->app->bind(
            EnrichmentInterface::class,
            config('enrichment.enrichment', Enrichment::class)
        );

        $this->app->bind(
            SerializerInterface::class,
            config('enrichment.serializer', Serializer::class)
        );

        $this->app->bind(
            ParserInterface::class,
            config('enrichment.parser', Parser::class)
        );
    }

    private function registerRepositories(): void
    {
        $this->app->singleton(
            Repositories::class,
            function (Application $application): Repositories {
                $repositories = new Repositories();

                /**
                 * @var array<string,string> $mapping
                 */
                $mapping = config('enrichment.repositories', []);

                foreach ($mapping as $target => $class) {
                    /**
                     * @var \Diffhead\PHP\DataEnrichmentKit\Interface\Repository $repository
                     */
                    $repository = $application->make($class);
                    $repositories->set($target, $repository);
                }

                return $repositories;
            }
        );
    }

    private function registerFacadeManagers(): void
    {
        $this->app->singleton(
            HttpManager::class,
            function (Application $application): HttpManager {
                return new HttpManager(
                    $application->make(Enricher::class),
                    $application->make(Message::class)
                );
            }
        );

        $this->app->singleton(
            ArrayManager::class,
            function (Application $application): ArrayManager {
                return new ArrayManager(
                    $application->make(Enricher::class)
                );
            }
        );
    }

    private function registerConfigsPublishment(): void
    {
        $this->publishes(
            [
                $this->configPath('config/enrichment.php') => config_path('enrichment.php'),
            ],
            'config'
        );
    }

    private function configPath(string $path): string
    {
        return sprintf('%s/../%s', __DIR__, $path);
    }
}
