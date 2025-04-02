<?php

namespace Weijiajia\IpAddress;

use Saloon\Http\SoloRequest;
use Weijiajia\SaloonphpLogsPlugin\Contracts\HasLoggerInterface;
use Weijiajia\SaloonphpHttpProxyPlugin\Contracts\ProxyManagerInterface;
use Weijiajia\SaloonphpLogsPlugin\HasLogger;
use Weijiajia\SaloonphpHttpProxyPlugin\HasProxy;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Saloon\Http\Faking\MockClient;
use Weijiajia\IpAddress\Contracts\Request as RequestContract;
use Saloon\Traits\Responses\HasResponse;

abstract class Request extends SoloRequest implements HasLoggerInterface,ProxyManagerInterface,RequestContract
{
    use HasLogger;
    use AcceptsJson;
    use HasProxy;
    use AlwaysThrowOnErrors;


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

        if ($dataObject instanceof HasResponse) {
            $dataObject->setResponse($response);
        }

        return $dataObject;
    }

}