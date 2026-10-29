<?php

namespace App\Controller\Admin;

use App\Entity\SocialMedia;
use App\Form\SocialMediaType;
use App\Repository\SocialMediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:"/social", name:"admin.social.")]
#[IsGranted('ROLE_USER')]
final class SocialMediaController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(SocialMediaRepository $socialMediaRepository): Response
    {
        return $this->render('admin/social_media/index.html.twig', [
            'social_media' => $socialMediaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $socialMedia = new SocialMedia();
        $form = $this->createForm(SocialMediaType::class, $socialMedia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($socialMedia);
            $entityManager->flush();

            $this->addFlash('success', 'Élément ajouté avec succès.');

            return $this->redirectToRoute('admin.social.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/social_media/new.html.twig', [
            'social_media' => $socialMedia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SocialMedia $socialMedia): Response
    {
        return $this->render('admin/social_media/show.html.twig', [
            'social_media' => $socialMedia,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SocialMedia $socialMedia, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SocialMediaType::class, $socialMedia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Modifications enregistrées.');

            return $this->redirectToRoute('admin.social.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/social_media/edit.html.twig', [
            'social_media' => $socialMedia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, SocialMedia $socialMedia, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$socialMedia->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($socialMedia);
            $entityManager->flush();
            $this->addFlash('success', 'Élément supprimé.');
        }

        return $this->redirectToRoute('admin.social.index', [], Response::HTTP_SEE_OTHER);
    }
}
