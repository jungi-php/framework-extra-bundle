<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

class ChangeUserResidentialAddressCommand
{
    private $userId;
    private $street;
    private $city;
    private $province;
    private $countryCode;

    public function __construct(string $userId, string $street, string $city, string $province, string $countryCode)
    {
        $this->userId = $userId;
        $this->street = $street;
        $this->city = $city;
        $this->province = $province;
        $this->countryCode = $countryCode;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function setProvince(string $province): void
    {
        $this->province = $province;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }
}
