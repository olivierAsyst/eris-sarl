<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Entity\HomeVideo;
use App\Entity\ImageGalery;
use App\Entity\Member;
use App\Entity\Partner;
use App\Entity\Realisation;
use App\Entity\Services;
use App\Entity\SocialMedia;
use App\Repository\ContactMessageRepository;
use App\Repository\RealisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminController extends AbstractController
{
    /** Tableau de bord : chiffres, derniers messages et points d'attention. */
    #[Route('/admin', name: 'app_admin')]
    public function index(EntityManagerInterface $em, ContactMessageRepository $messages, RealisationRepository $realisations): Response
    {
        $count = static fn (string $class): int => $em->getRepository($class)->count([]);

        return $this->render('admin/index.html.twig', [
            'counts' => [
                'services' => $count(Services::class),
                'realisations' => $count(Realisation::class),
                'galerie' => $count(ImageGalery::class),
                'membres' => $count(Member::class),
                'partenaires' => $count(Partner::class),
                'reseaux' => $count(SocialMedia::class),
                'videos' => $count(HomeVideo::class),
                'messages' => $count(ContactMessage::class),
            ],
            'latest_messages' => $messages->findLatest(5),
            'unsent' => $messages->count(['mailSent' => false]),
            'orphans' => $realisations->count(['service' => null]),
            'no_image' => $realisations->count(['imageUrl' => null]),
        ]);
    }
}
