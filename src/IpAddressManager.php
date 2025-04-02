<?php

namespace Weijiajia\IpAddress;

use Illuminate\Support\Manager;
use Weijiajia\IpAddress\Request;
use Weijiajia\IpAddress\Requests\MyIpRequest;
use Weijiajia\IpAddress\Requests\PconLineRequest;
use Weijiajia\IpAddress\Requests\Ip138Request;

class IpAddressManager extends Manager
{
    
    /**
     * 获取默认驱动名称
     *
     * @return string
     */
    public function getDefaultDriver(): ?string
    {
        return $this->config->get('ip-address.default');
    }

    /**
     * @param $driver
     * @return Request
     */
    public function driver($driver = null):Request
    {
        return parent::driver($driver);
    }

    protected function createMyipDriver(): Request
    {
        return $this->createConnector(MyIpRequest::class);
    }

    protected function createPconlineDriver(): Request
    {
        return $this->createConnector(PconLineRequest::class);
    }

    protected function createIp138Driver(): Request
    {
        return $this->createConnector(Ip138Request::class,['token' => $this->config->get('ip-address.ip138.token')]);
    }

    /**
     * 创建代理服务实例
     *
     * @param string|null $driver
     * @return Request
     */
    public function connector(?string $driver = null): Request
    {
        return $this->driver($driver);
    }
}
