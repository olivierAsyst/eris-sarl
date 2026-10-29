<?php

namespace App\Controller;

use App\Repository\RealisationRepository;
use App\Repository\ServicesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** Plan du site pour les moteurs de recherche (à déclarer dans Google Search Console). */
final class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'], format: 'xml')]
    public function index(ServicesRepository $services, RealisationRepository $realisations): Response
    {
        $abs = UrlGeneratorInterface::ABSOLUTE_URL;
        $urls = [];

        foreach ([
            ['app_home', '1.0', 'weekly'],
            ['app_services_front', '0.9', 'monthly'],
            ['app_realisation_front', '0.9', 'weekly'],
            ['app_about', '0.7', 'yearly'],
            ['app_front_member', '0.5', 'monthly'],
            ['app_front_image', '0.5', 'monthly'],
            ['app_contact', '0.8', 'yearly'],
        ] as [$route, $priority, $freq]) {
            $urls[] = ['loc' => $this->generateUrl($route, [], $abs), 'priority' => $priority, 'changefreq' => $freq, 'lastmod' => null];
        }

        foreach ($services->findAll() as $s) {
            $urls[] = [
                'loc' => $this->generateUrl('app_service_show', ['slug' => $s->getSlug()], $abs),
                'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $s->getUpdatedAt(),
            ];
        }

        foreach ($realisations->findAll() as $r) {
            $urls[] = [
                'loc' => $this->generateUrl('app_realisation_show', ['id' => $r->getId(), 'slug' => $r->getSlug()], $abs),
                'priority' => '0.7', 'changefreq' => 'yearly', 'lastmod' => $r->getUpdatedAt(),
            ];
        }

        $response = $this->render('sitemap.xml.twig', ['urls' => $urls]);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}
