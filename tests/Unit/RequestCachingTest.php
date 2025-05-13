<?php

use Saloon\CachePlugin\Contracts\Driver as CacheDriverContract;
use Saloon\CachePlugin\Drivers\InMemoryDriver;
use Saloon\CachePlugin\Drivers\PsrCacheDriver;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request as SaloonHttpRequest;
use Saloon\Http\Connector;
use Symfony\Component\Cache\Adapter\ArrayAdapter as SymfonyArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Weijiajia\IpAddress\Tests\Unit\CacheableTestRequest;
use Saloon\Enums\Method as SaloonMethod; // Alias Saloon\Enums\Method
use Weijiajia\IpAddress\IpResponse; // 导入 IpResponse

// Mock PendingRequest for cacheKey testing
function mockPendingRequest(string $url = 'http://example.com/test', array $query = [], array $headers = [], ?string $body = null): PendingRequest
{
    $mockConnector = new class extends Connector {
        public function resolveBaseUrl(): string
        {
            return 'http://example.com';
        }
    };
    // Use a concrete Saloon request for pending request creation if possible, or a generic one.
    $mockSaloonRequest = new class extends SaloonHttpRequest {
        protected SaloonMethod $method = SaloonMethod::GET;
        public function resolveEndpoint(): string
        {
            return '/test';
        }
    };

    $pendingRequest = new PendingRequest($mockConnector, $mockSaloonRequest);
    // Manually set the URL as it might not be formed correctly by default with this minimal setup
    $pendingRequest->setUrl($url);

    if (!empty($query)) {
        $pendingRequest->query()->merge($query);
    }
    if (!empty($headers)) {
        $pendingRequest->headers()->merge($headers);
    }
    if ($body !== null) {
        $pendingRequest->body()->set($body);
    }
    return $pendingRequest;
}


it('throws exception if no cache driver is provided', function () {
    $request = new CacheableTestRequest();
    $request->resolveCacheDriver();
})->throws(LogicException::class, 'Cache driver has not been set. Please provide a cache driver using withCacheDriver().');

it('resolves cache driver when provided', function () {
    $request = new CacheableTestRequest();
    // 使用 Mockery 进行模拟
    $mockDriver = \Mockery::mock(CacheDriverContract::class);
    $request->withCacheDriver($mockDriver);
    expect($request->resolveCacheDriver())->toBe($mockDriver);
});

it('returns default cache expiry', function () {
    $request = new CacheableTestRequest();
    expect($request->cacheExpiryInSeconds())->toBe(0);
});

it('sets and returns custom cache expiry', function () {
    $request = new CacheableTestRequest();
    $request->withCacheExpiry(3600);
    expect($request->cacheExpiryInSeconds())->toBe(3600);
});

it('returns null for cache key by default', function () {
    $request = new CacheableTestRequest();
    $pendingRequest = mockPendingRequest();
    $method = new ReflectionMethod(CacheableTestRequest::class, 'cacheKey');
    $method->setAccessible(true);
    expect($method->invoke($request, $pendingRequest))->toBeNull();
});

it('sets and returns string cache key', function () {
    $request = new CacheableTestRequest();
    $request->withCacheKey('my_custom_key');
    $pendingRequest = mockPendingRequest();
    $method = new ReflectionMethod(CacheableTestRequest::class, 'cacheKey');
    $method->setAccessible(true);
    expect($method->invoke($request, $pendingRequest))->toBe('my_custom_key');
});

it('sets and returns cache key from closure', function () {
    $request = new CacheableTestRequest();
    $pendingRequest = mockPendingRequest(url: 'http://api.example.com/data');

    $request->withCacheKey(function (PendingRequest $pendingReq) {
        return 'closure_key_' . md5($pendingReq->getUrl());
    });

    $method = new ReflectionMethod(CacheableTestRequest::class, 'cacheKey');
    $method->setAccessible(true);
    expect($method->invoke($request, $pendingRequest))->toBe('closure_key_' . md5('http://api.example.com/data'));
});

it('returns cacheable http methods', function () {
    $request = new CacheableTestRequest();
    $method = new ReflectionMethod(CacheableTestRequest::class, 'getCacheableMethods');
    $method->setAccessible(true);
    $methods = $method->invoke($request);
    expect($methods)->toBeArray()
        ->toContain(SaloonMethod::GET)
        ->toContain(SaloonMethod::POST);
});

function createRequestWithMemoryCache(): ?CacheableTestRequest
{
    $request = new CacheableTestRequest();
    $driver = null;
    if (class_exists(InMemoryDriver::class)) {
        $driver = new InMemoryDriver();
    } elseif (class_exists(PsrCacheDriver::class) && class_exists(SymfonyArrayAdapter::class) && class_exists(Psr16Cache::class)) {
        $psr16Cache = new Psr16Cache(new SymfonyArrayAdapter());
        $driver = new PsrCacheDriver($psr16Cache);
    } else {
        test()->markTestSkipped('InMemoryDriver or Symfony Cache for PsrCacheDriver not available for testing cache interactions.');
        return null;
    }
    $request->withCacheDriver($driver);
    $request->withCacheExpiry(3600);
    return $request;
}

it('caches a request on first successful call and serves from cache on second call', function () {
    $request = createRequestWithMemoryCache();
    if (!$request) return;

    $mockClient = new MockClient([
        CacheableTestRequest::class => MockResponse::make(['message' => 'Hello from API'], 200),
    ]);

    /** @var IpResponse $response1 */
    $response1 = $request->request([], $mockClient);
    expect($response1->isCached())->toBeFalse();
    expect($response1->json('message'))->toBe('Hello from API');
    $mockClient->assertSent(CacheableTestRequest::class);

    $mockClient->resetSentRequests();
    /** @var IpResponse $response2 */
    $response2 = $request->request([], $mockClient);
    expect($response2->isCached())->toBeTrue();
    expect($response2->json('message'))->toBe('Hello from API');
    $mockClient->assertNotSent(CacheableTestRequest::class);
});

it('does not cache a failed request', function () {
    $request = createRequestWithMemoryCache();
    if (!$request) return;

    $mockClient = new MockClient([
        CacheableTestRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    try {
        $request->request([], $mockClient);
    } catch (\Saloon\Exceptions\Request\RequestException $e) {
        // Expected
    }
    $mockClient->assertSent(CacheableTestRequest::class);

    $mockClient->addResponse(CacheableTestRequest::class, MockResponse::make(['message' => 'Success now'], 200));
    $mockClient->resetSentRequests();

    /** @var IpResponse $response2 */
    $response2 = $request->request([], $mockClient);
    expect($response2->isCached())->toBeFalse();
    expect($response2->json('message'))->toBe('Success now');
    $mockClient->assertSent(CacheableTestRequest::class);
});

it('respects disableCaching', function () {
    $request = createRequestWithMemoryCache();
    if (!$request) return;

    $mockClient = new MockClient([
        CacheableTestRequest::class => MockResponse::make(['message' => 'Live Data'], 200),
    ]);

    $request->request([], $mockClient);
    $mockClient->assertSent(CacheableTestRequest::class);
    $mockClient->resetSentRequests();

    $request->disableCaching();
    /** @var IpResponse $response */
    $response = $request->request([], $mockClient);

    expect($response->isCached())->toBeFalse();
    expect($response->json('message'))->toBe('Live Data');
    $mockClient->assertSent(CacheableTestRequest::class);
});

it('respects invalidateCache', function () {
    $request = createRequestWithMemoryCache();
    if (!$request) return;

    $mockData = ['message' => 'Initial Data'];
    // Use a closure for dynamic mock responses
    $mockClient = new MockClient([
        CacheableTestRequest::class => fn() => MockResponse::make($mockData, 200),
    ]);

    /** @var IpResponse $response1 */
    $response1 = $request->request([], $mockClient);
    expect($response1->isCached())->toBeFalse();
    expect($response1->json('message'))->toBe('Initial Data');
    $mockClient->assertSentCount(1);

    /** @var IpResponse $response2 */
    $response2 = $request->request([], $mockClient);
    expect($response2->isCached())->toBeTrue();
    expect($response2->json('message'))->toBe('Initial Data');
    $mockClient->assertSentCount(1);

    $request->invalidateCache();
    $mockData['message'] = 'New Data after invalidation';

    /** @var IpResponse $response3 */
    $response3 = $request->request([], $mockClient);
    expect($response3->isCached())->toBeFalse();
    expect($response3->json('message'))->toBe('New Data after invalidation');
    $mockClient->assertSentCount(2);
});

it('caches post requests if configured', function () {
    $postRequest = new class extends CacheableTestRequest {
        protected SaloonMethod $method = SaloonMethod::POST;
        public function __construct()
        {
            parent::__construct('/test-post');
        }
    };

    $driver = null;
    if (class_exists(InMemoryDriver::class)) {
        $driver = new InMemoryDriver();
    } elseif (class_exists(PsrCacheDriver::class) && class_exists(SymfonyArrayAdapter::class) && class_exists(Psr16Cache::class)) {
        $psr16Cache = new Psr16Cache(new SymfonyArrayAdapter());
        $driver = new PsrCacheDriver($psr16Cache);
    } else {
        test()->markTestSkipped('InMemoryDriver or Symfony Cache for PsrCacheDriver not available for testing POST cache.');
        return;
    }
    $postRequest->withCacheDriver($driver);
    $postRequest->withCacheExpiry(3600);

    $mockClient = new MockClient([
        get_class($postRequest) => MockResponse::make(['status' => 'created'], 201),
    ]);

    /** @var IpResponse $response1 */
    $response1 = $postRequest->request(['data' => 'sample'], $mockClient);
    expect($response1->isCached())->toBeFalse();
    $mockClient->assertSent(get_class($postRequest));

    $mockClient->resetSentRequests();
    /** @var IpResponse $response2 */
    $response2 = $postRequest->request(['data' => 'sample'], $mockClient);

    $reflectionMethod = new ReflectionMethod(CacheableTestRequest::class, 'getCacheableMethods');
    $reflectionMethod->setAccessible(true);
    $cacheableMethods = $reflectionMethod->invoke($postRequest); // Invoke on instance

    if (in_array(SaloonMethod::POST, $cacheableMethods)) {
        expect($response2->isCached())->toBeTrue();
        $mockClient->assertNotSent(get_class($postRequest));
    } else {
        expect($response2->isCached())->toBeFalse();
        $mockClient->assertSent(get_class($postRequest));
    }
});

// 最后，清理 Mockery
uses()->afterEach(function () {
    if (class_exists(\Mockery::class)) {
        \Mockery::close();
    }
});
