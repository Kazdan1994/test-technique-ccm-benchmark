<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Department;
use App\Repository\Exception\DepartmentNotFound;

final class DepartmentRepository
{
    /** @var ?array $data */
    private $dataByCode;

    private $lastModified;

    private $cityRepository;

    public function __construct(string $filePath)
    {
        $this->dataByCode = [];
        $handle = fopen($filePath, 'r');
        fgetcsv($handle); //Consume header
        while (($row = fgetcsv($handle)) !== false) {
            $this->dataByCode[$row[0]] = [
                'id' => $row[1],
                'name' => $row[2],
                'code' => $row[0],
            ];
        }
        $lms = filemtime($filePath);
        if (!$lms) {
            throw new \RuntimeException('Could not read lastmod.');
        }
        $this->lastModified = \DateTimeImmutable::createFromFormat('U', $lms);

        $this->cityRepository = new CityRepository(dirname(__FILE__) . '/../../db/cities.csv');
    }


    /**
     * @param string $code
     * @return Department
     * @throws DepartmentNotFound if departement not found
     */
    public function findOneByCode(string $code): Department
    {
        if (array_key_exists($code, $this->dataByCode) === true) {
            $department = new Department();
            $department->setId($this->dataByCode[$code]['id']);
            $department->setCode($this->dataByCode[$code]['code']);
            $department->setName($this->dataByCode[$code]['name']);

            return $department;
        }

        throw new DepartmentNotFound();
    }

    public function findAll(): array
    {
        $dataByCode = $this->dataByCode;

        array_walk($dataByCode, function (&$item) {
            $department = new Department();
            $department->setId($item['id']);
            $department->setCode($item['code']);
            $department->setName($item['name']);
            $item = $department;
        });

        asort($dataByCode);

        return $dataByCode;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastModified(): \DateTimeImmutable
    {
        return $this->lastModified;
    }
}
