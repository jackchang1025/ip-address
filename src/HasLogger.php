<?php

namespace Weijiajia;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Saloon\Http\PendingRequest;

trait HasLogger
{
    protected ?LoggerInterface $logger = null;

    public function withLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    protected function defaultRequestMiddle(): \Closure
    {
        return function (RequestInterface $request){
            $this->getLogger()?->debug('request', [
                'method'  => $request->getMethod(),
                'uri'     => (string) $request->getUri(),
                'headers' => $request->getHeaders(),
                'body'    => (string)$request->getBody(),
            ]);
            return $request;
        };
    }

    protected function defaultResponseMiddle(): \Closure
    {
        return function (ResponseInterface $response){
            $this->getLogger()?->debug('response', [
                'status'  => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body'    => (string) $response->getBody(),
            ]);
            return $response;
        };
    }

    public function bootHasLogger(PendingRequest $pendingRequest): void
    {
        $pendingRequest->getConnector()->middleware()->onRequest($this->defaultRequestMiddle());
        $pendingRequest->getConnector()->middleware()->onResponse($this->defaultResponseMiddle());
    }
}
