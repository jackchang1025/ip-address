<?php

namespace Weijiajia\IpAddress;

use Saloon\Http\SoloRequest;
use Saloon\Http\Request as SaloonRequest;
use Weijiajia\SaloonphpLogsPlugin\Contracts\HasLoggerInterface;
use Weijiajia\SaloonphpHttpProxyPlugin\Contracts\ProxyManagerInterface;
use Weijiajia\SaloonphpLogsPlugin\HasLogger;
use Weijiajia\SaloonphpHttpProxyPlugin\HasProxy;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Saloon\Http\Faking\MockClient;
use Weijiajia\IpAddress\Contracts\Request as RequestContract;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\ServerException;
abstract class Request extends SoloRequest implements HasLoggerInterface,ProxyManagerInterface,RequestContract
{
    use HasLogger;
    use AcceptsJson;
    use HasProxy;
    use AlwaysThrowOnErrors;

    public ?int $tries = 5;

    public function handleRetry(FatalRequestException|RequestException $exception, SaloonRequest $request): bool
    {
        return $exception instanceof FatalRequestException || $exception instanceof ServerException;
    }


    /**
     * Send a request synchronously
     */
    public function request(?MockClient $mockClient = null): IpResponse
    {
        $response = $this->connector()->send($this, $mockClient);

        $request = $response->getPendingRequest()->getRequest();

        if (!$request instanceof RequestContract) {
            throw new \Exception($request::class.' must implement RequestContract');
        }
        

        $dataObject = $request->createResponse($response);

        if ($dataObject instanceof WithResponse) {
            $dataObject->setResponse($response);
        }

        return $dataObject;
    }

}