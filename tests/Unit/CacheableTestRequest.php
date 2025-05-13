<?php

namespace Weijiajia\IpAddress\Tests\Unit;

use Saloon\Enums\Method;
use Saloon\Http\Response;
use Weijiajia\IpAddress\IpResponse;
use Weijiajia\IpAddress\Request;

class CacheableTestRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected ?string $endpointUrl = '/test') {}

    public function resolveEndpoint(): string
    {
        return $this->endpointUrl;
    }

    public function createResponse(Response $response): IpResponse
    {
        $responseData = [];
        try {
            $responseData = $response->json();
        } catch (\Exception $e) {
            // 如果响应不是 JSON 或为空，则忽略
        }
        $ipResponse = new IpResponse($responseData);
        // 确保 Saloon 的原始响应被设置，以便 HasResponse trait 中的方法 (如 isCached) 能工作
        $ipResponse->setResponse($response);
        return $ipResponse;
    }
}
