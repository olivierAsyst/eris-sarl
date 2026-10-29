<?php

namespace App\Controller;

use App\Repository\HomeVideoRepository;
use App\Repository\ImageGaleryRepository;
use App\Repository\MemberRepository;
use App\Repository\OurnumberRepository;
use App\Repository\PartnerRepository;
use App\Repository\RealisationRepository;
use App\Repository\ServicesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        MemberRepository $memberRepository,
        ImageGaleryRepository $imageGaleryRepository,
        PartnerRepository $partnerRepository,
        HomeVideoRepository $homeVideoRepository,
        RealisationRepository $realisationRepository,
        OurnumberRepository $ournumberRepository,
        ServicesRepository $servicesRepository,
    ): Response {
        return $this->render('home/index.html.twig', [
            'members' => $memberRepository->findBy([], null, 4),
            'images' => $imageGaleryRepository->findBy([], ['id' => 'DESC'], 4),
            'partners' => $partnerRepository->findAll(),
            'video' => $homeVideoRepository->findLatest(),
            'realisations' => $realisationRepository->findLastThreeRealisations(),
            'chiffre' => $ournumberRepository->findLatestNumber(),
            'services' => $servicesRepository->findBy([], ['id' => 'ASC']),
        ]);
    }
}
