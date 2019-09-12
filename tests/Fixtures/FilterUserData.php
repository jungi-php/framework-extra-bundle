<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

final class FilterUserData
{
    private $nameIsLike;
    private $ageGreaterThan;
    private $ageLowerThan;

    /**
     * @param string|null $nameIsLike
     * @param int|null    $ageGreaterThan
     * @param int|null    $ageLowerThan
     */
    public function __construct(?string $nameIsLike, ?int $ageGreaterThan, ?int $ageLowerThan)
    {
        $this->nameIsLike = $nameIsLike;
        $this->ageGreaterThan = $ageGreaterThan;
        $this->ageLowerThan = $ageLowerThan;
    }

    public function getNameIsLike(): ?string
    {
        return $this->nameIsLike;
    }

    public function getAgeGreaterThan(): ?int
    {
        return $this->ageGreaterThan;
    }

    public function getAgeLowerThan(): ?int
    {
        return $this->ageLowerThan;
    }
}
