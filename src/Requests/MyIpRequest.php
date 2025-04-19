<?php

namespace Weijiajia\IpAddress\Requests;

use Weijiajia\IpAddress\Request;
use Saloon\Enums\Method;
use Saloon\Http\Response;
use Weijiajia\IpAddress\IpResponse;
use GuzzleHttp\RequestOptions;

class MyIpRequest extends Request
{
    protected Method $method = Method::GET;


    public function resolveEndpoint(): string
    {
        return 'http://api.ip.cc';
    }

    public function defaultHeaders(): array
    {
        return [
           'Accept'             => '*/*',
            'Accept-Encoding'    => 'gzip, deflate, br, zstd',
            'Accept-Language'    => 'en,zh-CN;q=0.9,zh;q=0.8',
            'Connection'         => 'keep-alive',
            'Sec-Ch-Ua'          => '"Not(A:Brand";v="99", "Google Chrome";v="133", "Chromium";v="133"',
            'Sec-Ch-Ua-Mobile'   => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Sec-Fetch-Dest'     => 'empty',
            'Sec-Fetch-Mode'     => 'cors',
            'Sec-Fetch-Site'     => 'same-site',
            'User-Agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
        ];
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        return empty($response->json('country_code')) || empty($response->json('timezone'));
    }


    public function createResponse(Response $response): IpResponse
    {
        // 获取原始响应内容
        $data = $response->json();
        $data['proxy'] = $response->getPendingRequest()->config()->get(RequestOptions::PROXY);

        return new IpResponse($data);
    }
}