<?php

namespace App\Tests\units\App\Repository;

use App\Repository\CityRepository;
use App\Repository\CitySQLiteRepository;
use PHPUnit\Framework\TestCase;

class CityRepositoryTest extends TestCase
{
    private CityRepository $cityRepository;
    private CitySQLiteRepository $citySQLiteRepository;

    public function setUp(): void
    {
        $this->cityRepository = new CityRepository(dirname(__FILE__) . '/../../../../db/cities.csv');
        $this->citySQLiteRepository = new CitySQLiteRepository(dirname(__FILE__) . '/../../../../db/cities.sqlite');
    }

    public function testFetchByDepartmentId()
    {
        $this->assertInstanceOf(
            CityRepository::class,
            $this->cityRepository
        );
    }

    public function testFetchByDepartmentIdWithSQLite()
    {
        $this->assertInstanceOf(
            CitySQLiteRepository::class,
            $this->citySQLiteRepository
        );
    }
}
