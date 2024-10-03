<?php

use Weijiajia\Exception\IpLookupException;
use Weijiajia\IpConnector;
use Weijiajia\Requests\Ip138Request;
use Weijiajia\Requests\PconLineRequest;
use Weijiajia\Responses\IpResponse;

test('example',  function () {
    expect(true)->toBeTrue();
});


test('pconline example',  function () {

    $connector = new IpConnector();

    //可选:设置超时
    $connector->config()->add('timeout',30);

    $request = new PconLineRequest('172.16.30.10');
    $response = $connector->send($request);

    /**
     * @var IpResponse $ipInfo
     */
    $ipInfo = $response->dto();

    expect($ipInfo)
        ->toBeInstanceOf(IpResponse::class)
        ->and($ipInfo->getIp())
        ->toBe('172.16.30.10')
        ->and($ipInfo->getAddr())
        ->not
        ->toBeNull();
});


test('Ip138Request example',  function () {

    $connector = new IpConnector();

    $request = new Ip138Request('xxxxxx', '172.16.30.10');
    $response = $connector->send($request);

    /**
     * @var IpResponse $ipInfo
     */
    $ipInfo = $response->dto();

})->throws(IpLookupException::class);