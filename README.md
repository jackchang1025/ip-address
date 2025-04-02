# Ip Address

Ip Address 是一个 IP 地址信息查询系统,集成了多个 IP 地理位置服务

## 支持平台
- [ip138](http://api.ipshudi.com)
- [pconline](https://whois.pconline.com.cn)

## 特性

- 支持多个 IP 地理位置服务提供商 (目前支持 ip138 和 pconline)
- 标准化的响应格式
- 灵活的日志记录功能
- 错误处理


## 要求

- PHP 8.2+
- Composer

## 安装

通过 Composer 安装:

```bash

composer require weijiajia/ip-address
```

首先，发布配置文件：

```bash
php artisan vendor:publish --provider="Weijiajia\IpAddress\IpAddressServiceProvider"
```

这将在 `config/http-proxy-manager.php` 创建配置文件。

## 使用方法
### 使用 pconline

```php

use Weijiajia\IpConnector;
use Weijiajia\Requests\PconLineRequest;

$connector = new IpConnector();

// 可选: 设置日志
$logger = new YourLoggerImplementation(); // 替换为您的实际日志实现
$connector->withLogger($logger);

//可选:设置超时
$connector->config()->add('timeout',30);

$request = new PconLineRequest('your ip address');
$response = $connector->send($request);

$ipInfo = $response->dto();
echo $ipInfo->getCity(); // 输出城市信息
echo $ipInfo->getAddr(); // 输出详细地址
```

### 使用 Ip138Request

```php
use Weijiajia\IpConnector;
use Weijiajia\Requests\Ip138Request;

$connector = new IpConnector();

$request = new Ip138Request($token, 'your ip address');
$response = $connector->send($request);

$ipInfo = $response->dto();
echo $ipInfo->getCity(); // 输出城市信息
echo $ipInfo->getAddr(); // 输出详细地址
```

### 错误处理

```php
use Weijiajia\IpConnector;
use Weijiajia\Requests\PconLineRequest;
use Weijiajia\Exceptions\IpLookupException;

$connector = new IpConnector();

try {
    $request = new PconLineRequest('invalid_ip');
    $response = $connector->send($request);
    $ipInfo = $response->dto();
} catch (IpLookupException $e) {
    echo "IP 查询失败: " . $e->getMessage();
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage();
}
```

## 扩展

要添加新的 IP 地理位置服务提供商,只需创建一个新的 Request 类并实现必要的方法。例如:

```php
namespace Weijiajia\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class NewProviderRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $ip)
    {
    }

    public function resolveEndpoint(): string
    {
        return 'https://api.newprovider.com/ip-lookup';
    }


    // 实现必要的方法...
    public function createDtoFromResponse(Response $response): IpResponse
    {
        $json = $response->json();
        
        return new IpResponse([
            'city' => $response->json('city'),
            'addr' => $response->json('addr'),
            'ip'   => $response->json('ip'),
            ...
        ]);
    }
}

// 使用新的 IP 地理位置服务提供商
$connector = new IpConnector();

$request = new NewProviderRequest('your ip address');
$response = $connector->send($request);

$ipInfo = $response->dto();
echo $ipInfo->getCity(); // 输出城市信息
echo $ipInfo->getAddr(); // 输出详细地址
```

## 参考文档
- [saloon 文档](https://docs.saloon.dev/)

## 贡献指南

我们欢迎并感谢任何形式的贡献。以下是一些贡献的方式:

1. 报告 Bug
2. 提交功能请求
3. 提交代码改进
4. 改进文档

### 提交 Pull Request

1. Fork 本仓库
2. 创建您的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交您的改动 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建一个 Pull Request

## 许可证
IP Address 是开源软件,基于 [MIT 许可证](LICENSE.md)。
