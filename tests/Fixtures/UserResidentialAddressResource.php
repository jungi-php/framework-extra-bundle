<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

class UserResidentialAddressResource
{
    private $street;
    private $city;
    private $province;
    private $countryCode;

    public function __construct(string $street, string $city, string $province, string $countryCode)
    {
        $this->street = $street;
        $this->city = $city;
        $this->province = $province;
        $this->countryCode = $countryCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}
