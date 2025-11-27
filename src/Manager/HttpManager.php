<?php

declare(strict_types=1);

namespace Diffhead\PHP\LaravelDataEnrichment\Manager;

use BackedEnum;
use Diffhead\PHP\DataEnrichmentKit\Enricher;
use Diffhead\PHP\DataEnrichmentKit\Header;
use Diffhead\PHP\DataEnrichmentKit\Message;
use Psr\Http\Message\MessageInterface;

class HttpManager extends AbstractManager
{
    private BackedEnum $requestsHeader = Header::XEnrichmentRequest;

    public function __construct(
        protected Enricher $enricher,
        private Message $message,
    ) {}

    public function useHeader(BackedEnum $header): static
    {
        $this->requestsHeader = $header;

        return $this;
    }

    public function setRequests(MessageInterface $message): MessageInterface
    {
        $header = $this->requestsHeader;
        $requests = $this->getRequests();

        return $this->message->setRequests($message, $header, $requests);
    }

    public function enrichMessage(MessageInterface $message): MessageInterface
    {
        $requests = $this->message->getRequests($message, $this->requestsHeader);

        if ($requests->count()) {
            $payload = $this->message->getPayload($message);
            $enriched = $this->enricher->enrich($payload, $requests);
            $message = $this->message->setPayload($message, $enriched);
        }

        return $message;
    }
}
