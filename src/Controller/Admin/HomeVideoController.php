<?php

namespace App\Controller\Admin;

use App\Entity\HomeVideo;
use App\Form\HomeVideoType;
use App\Repository\HomeVideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:"/video", name:"admin.video.")]
#[IsGranted('ROLE_USER')]
final class HomeVideoController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(HomeVideoRepository $homeVideoRepository): Response
    {
        return $this->render('admin/home_video/index.html.twig', [
            'home_videos' => $homeVideoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $homeVideo = new HomeVideo();
        $form = $this->createForm(HomeVideoType::class, $homeVideo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($homeVideo);
            $entityManager->flush();

            $this->addFlash('success', 'Élément ajouté avec succès.');

            return $this->redirectToRoute('admin.video.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/home_video/new.html.twig', [
            'home_video' => $homeVideo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(HomeVideo $homeVideo): Response
    {
        return $this->render('admin/home_video/show.html.twig', [
            'home_video' => $homeVideo,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HomeVideo $homeVideo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HomeVideoType::class, $homeVideo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Modifications enregistrées.');

            return $this->redirectToRoute('admin.video.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/home_video/edit.html.twig', [
            'home_video' => $homeVideo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, HomeVideo $homeVideo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$homeVideo->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($homeVideo);
            $entityManager->flush();
            $this->addFlash('success', 'Élément supprimé.');
        }

        return $this->redirectToRoute('admin.video.index', [], Response::HTTP_SEE_OTHER);
    }
}
