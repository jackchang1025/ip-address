<?php

use Weijiajia\IpAddress\Requests\Ip138Request;
use Weijiajia\IpAddress\Requests\PconLineRequest;
use Weijiajia\IpAddress\IpResponse;
use  Weijiajia\IpAddress\Requests\MyIpRequest;

test('example',  function () {
    expect(true)->toBeTrue();
});


test('pconline example',  function () {

    $request = new MyIpRequest();

    $request->withProxyEnabled(false);
    $ipInfo = $request->request();


    $request = new PconLineRequest($ipInfo->getIp());
    $request->withProxyEnabled(false);
    $response = $request->request();

    expect($response)
        ->toBeInstanceOf(IpResponse::class)
        ->and($response->getIp())
        ->toBe($ipInfo->getIp())
        ->and($response->getAddr())
        ->not
        ->toBeNull();
});


test('Ip138Request example',  function () {


    $request = new Ip138Request('xxxxxx', '172.16.30.10');
    $request->withProxyEnabled(false);
    $request->request();

})->throws(\Saloon\Exceptions\Request\RequestException::class);


it('myip example',  function () {
    
    $request = new MyIpRequest();

    $request->withProxyEnabled(false);
    $ipInfo = $request->request();

    expect($ipInfo)
        ->toBeInstanceOf(IpResponse::class)
        ->and($ipInfo->getIp())
        ->not
        ->toBeNull();

});