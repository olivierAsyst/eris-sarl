<?php

namespace App\Controller;

use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontMemberController extends AbstractController
{
    #[Route('/equipe', name: 'app_front_member', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        return $this->render('front_member/index.html.twig', [
            'members' => $memberRepository->findAll(),
        ]);
    }

    /** Ancienne adresse conservée pour ne pas casser les liens existants. */
    #[Route('/front/member', name: 'app_front_member_legacy', methods: ['GET'])]
    public function legacy(): Response
    {
        return $this->redirectToRoute('app_front_member', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
