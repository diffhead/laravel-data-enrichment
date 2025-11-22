<?php

declare(strict_types=1);

namespace Diffhead\PHP\LaravelDataEnrichment\Middleware;

use Closure;
use Diffhead\PHP\LaravelDataEnrichment\Facade\HttpEnrichment;
use Illuminate\Http\Request;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

class PinRequestsToResponse
{
    public function __construct(
        private PsrHttpFactory $psrFactory,
        private HttpFoundationFactory $httpFactory,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /**
         * @var \Symfony\Component\HttpFoundation\Response $response
         */
        $response = $next($request);
        $message = $this->psrFactory->createResponse($response);

        return $this->httpFactory->createResponse(
            HttpEnrichment::setRequests($message)
        );
    }
}
