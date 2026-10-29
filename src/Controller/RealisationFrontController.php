<?php

namespace App\Controller;

use App\Entity\Realisation;
use App\Repository\RealisationRepository;
use App\Repository\ServicesRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class RealisationFrontController extends AbstractController
{
    #[Route('/realisations', name: 'app_realisation_front', methods: ['GET'])]
    public function index(Request $request, RealisationRepository $realisationRepository, ServicesRepository $servicesRepository): Response
    {
        // Filtre facultatif par service : /realisations?service=genie-civil-infrastructures
        $current = null;
        if ($slug = $request->query->getString('service')) {
            $current = $servicesRepository->findOneBy(['slug' => $slug]);
        }

        return $this->render('realisation_front/index.html.twig', [
            'realisations' => $current
                ? $realisationRepository->findBy(['service' => $current], ['id' => 'DESC'])
                : $realisationRepository->findAllOrderedByDate(),
            'services' => $servicesRepository->findBy([], ['id' => 'ASC']),
            'current_service' => $current,
        ]);
    }

    #[Route('/realisations/{id}/{slug}', name: 'app_realisation_show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS, 'slug' => Requirement::ASCII_SLUG], defaults: ['slug' => ''])]
    public function show(#[MapEntity(id: 'id')] Realisation $realisation, string $slug, RealisationRepository $realisationRepository): Response
    {
        // URL canonique unique (évite le contenu dupliqué si le nom du projet change)
        if ($slug !== $realisation->getSlug()) {
            return $this->redirectToRoute('app_realisation_show', ['id' => $realisation->getId(), 'slug' => $realisation->getSlug()], Response::HTTP_MOVED_PERMANENTLY);
        }

        $related = $realisation->getService()
            ? $realisationRepository->findBy(['service' => $realisation->getService()], ['id' => 'DESC'], 4)
            : $realisationRepository->findLastThreeRealisations();

        return $this->render('realisation_front/show.html.twig', [
            'realisation' => $realisation,
            'related' => array_slice(array_filter($related, static fn (Realisation $r) => $r->getId() !== $realisation->getId()), 0, 3),
        ]);
    }
}
