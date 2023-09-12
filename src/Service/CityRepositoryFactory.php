<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class CityRepositoryFactory
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(): object
    {
        return $this->container->get('app.city_repository');
    }
}
