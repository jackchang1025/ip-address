<?php

use Weijiajia\IpAddress\Requests\Ip138Request;
use Weijiajia\IpAddress\Requests\PconLineRequest;
use Weijiajia\IpAddress\IpResponse;
use  Weijiajia\IpAddress\Requests\ApiIpCcRequest;

test('example',  function () {
    expect(true)->toBeTrue();
});


test('pconline example',  function () {

    $request = new ApiIpCcRequest();

    $request->withProxyEnabled(false)->disableCaching();
    $ipInfo = $request->request();


    $request = new PconLineRequest($ipInfo->getIp());
    $request->withProxyEnabled(false)->disableCaching();
    $response = $request->request();

    expect($response)
        ->toBeInstanceOf(IpResponse::class)
        ->and($response->getAddr())
        ->not
        ->toBeNull();
});


test('Ip138Request example',  function () {


    $request = new Ip138Request('xxxxxx', '172.16.30.10');
    $request->withProxyEnabled(false)->disableCaching();
    $request->request();

})->throws(\Saloon\Exceptions\Request\RequestException::class);


it('myip example',  function () {
    
    $request = new ApiIpCcRequest();

    $request->withProxyEnabled(false)->disableCaching();
    $ipInfo = $request->request();

    expect($ipInfo)
        ->toBeInstanceOf(IpResponse::class)
        ->and($ipInfo->getIp())
        ->not
        ->toBeNull();

});