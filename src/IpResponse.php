<?php

namespace Weijiajia\IpAddress;

use Saloon\Repositories\ArrayStore;
use Saloon\Traits\Responses\HasResponse;
use Saloon\Contracts\DataObjects\WithResponse;

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
