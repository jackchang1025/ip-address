<?php

namespace Weijiajia\IpAddress\Requests;

use Weijiajia\IpAddress\Request;
use Saloon\Enums\Method;
use Saloon\Http\Response;
use Weijiajia\IpAddress\IpResponse;
use GuzzleHttp\RequestOptions;

class ApiIpCcRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(public ?string $ip = null)
    {
    }

    public function resolveEndpoint(): string
    {
        return "http://api.ip.cc/{$this->ip}";
    }

    public function defaultHeaders(): array
    {
        return [
           'Accept'             => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Encoding'    => 'gzip, deflate',
            'Accept-Language'    => 'en-US,en;q=0.9',
            'Connection'         => 'keep-alive',
            'Sec-Ch-Ua'          => '"Not(A:Brand";v="99", "Google Chrome";v="133", "Chromium";v="133"',
            'Sec-Ch-Ua-Mobile'   => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Sec-Fetch-Dest'     => 'empty',
            'Sec-Fetch-Mode'     => 'cors',
            'Sec-Fetch-Site'     => 'same-site',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
        ];
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        if($response->serverError() || $response->clientError()){
            return true;
        }
        
        try{

            $data = $response->json();
            if(empty($data['country_code']) || empty($data['timezone'])){
                return true;
            }

            return null;

        }catch(\JsonException $e){
            return true;
        }
    }


    public function createResponse(Response $response): IpResponse
    {
        // 获取原始响应内容
        $data = $response->json();
        $data['proxy'] = $response->getPendingRequest()->config()->get(RequestOptions::PROXY);

        return new IpResponse($data);
    }
}