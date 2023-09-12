<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
        $routes->add('game_api', '/game/api')->controller([$this, 'apiGame']);
    }

    /**
     * Get 20 unique random winners, between 1 and 1000, not in an array of losers
     */
    public function apiGame(): JsonResponse
    {
        $losers = [
            2, 56, 345, 674, 234, 764, 543, 123, 324, 9, 78, 12, 94, 12, 50, 5, 13
        ];

        // Create an array of all possible winners from 1 to 1000
        $possibleWinners = range(1, 1000);

        // Remove losers from the array of possible winners
        $possibleWinners = array_diff($possibleWinners, $losers);

        // Shuffle the array of possible winners
        shuffle($possibleWinners);

        // Take the first 20 elements as winners
        $winners = array_slice($possibleWinners, 0, 20);


        return new JsonResponse(['winners' => $winners]);
    }
}
