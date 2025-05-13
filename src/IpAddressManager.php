<?php

namespace Weijiajia\IpAddress;

use Illuminate\Support\Manager;
use Weijiajia\IpAddress\Request;
use Weijiajia\IpAddress\Requests\ApiIpCcRequest;
use Weijiajia\IpAddress\Requests\PconLineRequest;
use Weijiajia\IpAddress\Requests\Ip138Request;
use Weijiajia\IpAddress\Requests\IpDecodoRequest;

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
    public function driver($driver = null): Request
    {
        return parent::driver($driver);
    }

    protected function createApiIpCcDriver(): Request
    {
        return $this->container->make(ApiIpCcRequest::class);
    }

    protected function createPconlineDriver(): Request
    {
        return $this->container->make(PconLineRequest::class);
    }

    protected function createIp138Driver(): Request
    {
        return $this->container->make(Ip138Request::class, ['token' => $this->config->get('ip-address.ip138.token')]);
    }

    protected function createIpDecodoDriver(): Request
    {
        return $this->container->make(IpDecodoRequest::class);
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
