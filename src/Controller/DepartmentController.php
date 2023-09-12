<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\DepartmentRepository;
use App\Repository\Exception\DepartmentNotFound;
use App\Service\CityRepositoryFactory;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class DepartmentController extends AbstractController
{
    private $cache;
    private $cityRepositoryFactory;

    public function __construct(AdapterInterface $cache, CityRepositoryFactory $cityRepositoryFactory)
    {
        $this->cache = $cache;
        $this->cityRepositoryFactory = $cityRepositoryFactory;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(
        Request              $request,
        DepartmentRepository $departmentRepository,
        SluggerInterface     $slugger,
        RouterInterface      $router,
        TranslatorInterface  $translator
    ): Response {
        $departmentCode = $request->get('code');
        $cacheKey = 'department_' . $departmentCode;

        try {
            // Try to get department data from cache
            $department = $this->cache->get($cacheKey, function () use ($departmentRepository, $departmentCode) {
                return $departmentRepository->findOneByCode($departmentCode);
            });
        } catch (DepartmentNotFound $e) {
            throw new NotFoundHttpException();
        }

        $queryString = '';
        if (!empty($request->getQueryString())) {
            $queryString = '?' . $request->getQueryString();
        }

        $departmentUrl = $router->generate(
            'department',
            [
                'code' => $department->getCode(),
                'name' => strtolower($slugger->slug($department->getName()))
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $trueUrl = $departmentUrl . $queryString;
        if ($trueUrl !== $request->getUri()) {
            return $this->redirect($trueUrl, Response::HTTP_MOVED_PERMANENTLY);
        }

        // Define the cache key for department cities
        $citiesCacheKey = 'department_cities_' . $departmentCode;

        // Try to get cities data from cache
        $cities = $this->cache->get($citiesCacheKey, function () use ($department, $translator) {
            $cityRepository = $this->cityRepositoryFactory->create();
            $method = method_exists($cityRepository, 'fetchByDepartmentId') ? 'fetchByDepartmentId' : 'findCitiesByDepartmentId';

            $cities = call_user_func([$cityRepository, $method], $department->getId());

            usort($cities, function (City $a, City $b) {
                return strcmp($a->getName(), $b->getName());
            });

            return $cities;
        });

        $viewParameters = [
            'department' => $department,
            'description' => $translator->trans(
                'department.description %deparmentLabel%',
                ['%deparmentLabel%' => $department->getName()]
            ),
            'url' => $departmentUrl,
            'cities' => $cities
        ];

        return $this->render('department.html.twig', $viewParameters);
    }
}
