<?php

namespace App\Controller;

use App\Entity\Services;
use App\Repository\ServicesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class ServicesFrontController extends AbstractController
{
    #[Route('/service', name: 'app_services_front', methods: ['GET'])]
    public function index(ServicesRepository $servicesRepository): Response
    {
        return $this->render('services_front/index.html.twig', [
            'services' => $servicesRepository->findBy([], ['id' => 'ASC']),
        ]);
    }

    #[Route('/service/{slug}', name: 'app_service_show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function show(string $slug, ServicesRepository $servicesRepository): Response
    {
        $service = $servicesRepository->findOneBy(['slug' => $slug]);
        if (!$service instanceof Services) {
            throw new NotFoundHttpException('Service introuvable');
        }

        return $this->render('services_front/show.html.twig', [
            'service' => $service,
            'others' => array_filter(
                $servicesRepository->findBy([], ['id' => 'ASC']),
                static fn (Services $s) => $s->getId() !== $service->getId()
            ),
        ]);
    }
}
