<?php

namespace Weijiajia\IpAddress\Requests;

use JsonException;
use Weijiajia\IpAddress\Exception\IpLookupException;
use Weijiajia\IpAddress\IpResponse;
use Saloon\Enums\Method;
use Saloon\Http\Response;
use Weijiajia\IpAddress\Request;
use GuzzleHttp\RequestOptions;

class Ip138Request extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(public string $token, public ?string $ip = null, public string $dataType = 'jsonp')
    {
    }


    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return 'http://api.ipshudi.com/ipdata/';
    }

    /**
     * @param Response $response
     * @return bool|null
     * @throws JsonException
     */
    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->json()['ret'] !== 'ok';
    }

    public function defaultHeaders(): array
    {
        return [
            'token' => $this->token,
        ];
    }

    public function defaultQuery(): array
    {
        return [
            'ip'       => $this->ip,
            'datatype' => $this->dataType,
        ];
    }

    /**
     * @param Response $response
     * @return IpResponse
     * @throws IpLookupException
     * @throws JsonException
     */
    public function createResponse(Response $response): IpResponse
    {
        $json = $response->json();
        if (!isset($json['data']) || !is_array($json['data']) || count($json['data']) < 3) {
            throw new IpLookupException($response, $response->body());
        }

        return new IpResponse([
            'city' => $response->json('data')[2],
            'addr' => $response->json('addr'),
            'ip'   => $response->json('ip'),
            'proxy' => $response->getPendingRequest()->config()->get(RequestOptions::PROXY),
        ]);
    }
}
