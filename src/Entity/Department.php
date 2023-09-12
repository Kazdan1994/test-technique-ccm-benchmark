<?php

namespace App\Entity;

final class Department
{

    /** @var int $id */
    private $id;

    /** @var string $id */
    private $name;

    /** @var string $code */
    private $code;

    /** @var array $cities */
    private $cities;

    public function setId(int $id):void
    {
        $this->id = $id;
    }

    public function getId():int
    {
        return $this->id;
    }

    public function setName(string $name):void
    {
        $this->name = $name;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function setCities(array $cities): void
    {
        $this->cities = $cities;
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function setCode(string $code):void
    {
        $this->code = $code;
    }

    public function getCode():string
    {
        return $this->code;
    }
}
