<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\CitySQLiteRepository;
use App\Repository\DepartmentRepository;
use App\Repository\Exception\DepartmentNotFound;
use App\Service\CityRepositoryFactory;
use InvalidArgumentException;
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
    public function __invoke(
        Request              $request,
        DepartmentRepository $departmentRepository,
        CityRepositoryFactory $cityRepositoryFactory,
        SluggerInterface     $slugger,
        RouterInterface      $router,
        TranslatorInterface  $translator
    ): Response
    {
        $cityRepository = $cityRepositoryFactory->create();

        try {
            $department = $departmentRepository->findOneByCode($request->get('code'));
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

        $method = method_exists(
            $cityRepository, 'fetchByDepartmentId'
        ) ? 'fetchByDepartmentId' : 'findCitiesByDepartmentId';

        $cities = call_user_func([
            $cityRepository,
            $method
        ],
            $department->getId()
        );

        usort($cities, function (City $a, City $b) {
            return strcmp($a->getName(), $b->getName());
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
