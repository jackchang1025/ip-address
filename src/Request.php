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
use Saloon\Exceptions\Request\ServerException;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\PsrCacheDriver;
use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;
use GuzzleHttp\RequestOptions;
use Closure;
use LogicException;

abstract class Request extends SoloRequest implements HasLoggerInterface, ProxyManagerInterface, RequestContract, Cacheable
{
    use HasLogger;
    use AcceptsJson;
    use HasProxy;
    use AlwaysThrowOnErrors;
    use HasCaching;

    protected Driver $cacheDriver;

    protected int $cacheExpiry = 0;

    protected null|string|Closure $cacheKey = null;

    public ?int $tries = 5;

    public function handleRetry(FatalRequestException|RequestException $exception, SaloonRequest $request): bool
    {
        return $exception instanceof FatalRequestException || $exception instanceof ServerException || $exception instanceof ForbiddenException || $exception instanceof RequestException;
    }

    public function resolveCacheDriver(): Driver
    {
        if (isset($this->cacheDriver)) {
            return $this->cacheDriver;
        }
        throw new LogicException('Cache driver has not been set. Please provide a cache driver using withCacheDriver().');
    }

    public function cacheExpiryInSeconds(): int
    {
        return $this->cacheExpiry;
    }

    public function withCacheExpiry(int $seconds): static
    {
        $this->cacheExpiry = $seconds;
        return $this;
    }

    public function withCacheDriver(Driver $driver): static
    {
        $this->cacheDriver = $driver;
        return $this;
    }

    public function withCacheKey(string|Closure $cacheKey): static
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }

    protected function getCacheableMethods(): array
    {
        return [Method::GET, Method::POST];
    }

    protected function cacheKey(PendingRequest $pendingRequest): ?string
    {
        if (is_callable($this->cacheKey)) {
            return call_user_func($this->cacheKey, $pendingRequest);
        }

        if (is_string($this->cacheKey)) {
            return $this->cacheKey;
        }

        // 如果用户未提供自定义键，则返回 null，让 Saloon 插件使用其默认键策略。
        return null;
    }


    /**
     * Send a request synchronously
     */
    public function request(?array $params = [], ?MockClient $mockClient = null): IpResponse
    {
        $this->config()->merge($params);

        $response = $this->connector()->send($this, $mockClient);

        $request = $response->getPendingRequest()->getRequest();

        if (!$request instanceof RequestContract) {
            throw new \Exception($request::class . ' must implement RequestContract');
        }

        $dataObject = $request->createResponse($response);

        if ($dataObject instanceof WithResponse) {
            $dataObject->setResponse($response);
        }

        return $dataObject;
    }
}
