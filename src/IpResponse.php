<?php

namespace Weijiajia\IpAddress;

use Saloon\Repositories\ArrayStore;
use Saloon\Traits\Responses\HasResponse;
use Saloon\Contracts\DataObjects\WithResponse;

/**
 * @method bool isCached()
 * @method bool isFromCache()
 * @method mixed|null json(string|null $key = null, mixed $default = null)
 * @method string body()
 * @method int status()
 * @method bool successful()
 * @method bool ok()
 * @method bool redirect()
 * @method bool failed()
 * @method bool clientError()
 * @method bool serverError()
 * @method array headers()
 * @method \Illuminate\Http\Client\Response|\Psr\Http\Message\ResponseInterface toPsrResponse()
 * @method \Saloon\Http\PendingRequest getPendingRequest()
 */
class IpResponse extends ArrayStore implements WithResponse
{
    use HasResponse;

    public function getCountry(): ?string
    {
        return $this->get('country');
    }

    public function getProvince(): ?string
    {
        return $this->get('province');
    }

    public function getCountryCode(): ?string
    {
        return $this->get('country_code');
    }

    public function getProvinceCode(): ?string
    {
        return $this->get('province_code');
    }

    public function getCity(): ?string
    {
        return $this->get('city');
    }

    public function getCityCode(): ?string
    {
        return $this->get('city_code');
    }

    public function getAddr(): ?string
    {
        return $this->get('addr');
    }

    public function getIp(): ?string
    {
        return $this->get('ip');
    }

    public function getZipCode(): ?string
    {
        return $this->get('zip_code');
    }

    public function getTimezone(): ?string
    {
        return $this->get('timezone');
    }

    public function getLatitude(): ?string
    {
        return $this->get('latitude');
    }

    public function getLongitude(): ?string
    {
        return $this->get('longitude');
    }

    public function getProxy(): ?string
    {
        return $this->get('proxy');
    }
}
