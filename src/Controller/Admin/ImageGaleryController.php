<?php

namespace App\Controller\Admin;

use App\Entity\ImageGalery;
use App\Form\ImageGaleryType;
use App\Repository\ImageGaleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/image/galery', name:"admin.galery.")]
#[IsGranted('ROLE_USER')]
final class ImageGaleryController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(ImageGaleryRepository $imageGaleryRepository): Response
    {
        return $this->render('admin/image_galery/index.html.twig', [
            'image_galeries' => $imageGaleryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'create', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $imageGalery = new ImageGalery();
        $form = $this->createForm(ImageGaleryType::class, $imageGalery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($imageGalery);
            $entityManager->flush();

            $this->addFlash('success', 'Élément ajouté avec succès.');

            return $this->redirectToRoute('admin.galery.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/image_galery/new.html.twig', [
            'image_galery' => $imageGalery,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(ImageGalery $imageGalery): Response
    {
        return $this->render('admin/image_galery/show.html.twig', [
            'image_galery' => $imageGalery,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ImageGalery $imageGalery, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ImageGaleryType::class, $imageGalery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Modifications enregistrées.');

            return $this->redirectToRoute('admin.galery.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/image_galery/edit.html.twig', [
            'image_galery' => $imageGalery,
            'form' => $form->createView(),
        ]);
    }
    // #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, ImageGalery $imageGalery, EntityManagerInterface $entityManager): Response
    // {
    //     $form = $this->createForm(ImageGaleryType::class, $imageGalery);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->flush();

    //         return $this->redirectToRoute('admin.galery.index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('admin/image_galery/edit.html.twig', [
    //         'image_galery' => $imageGalery,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, ImageGalery $imageGalery, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$imageGalery->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($imageGalery);
            $entityManager->flush();
            $this->addFlash('success', 'Élément supprimé.');
        }

        return $this->redirectToRoute('admin.galery.index', [], Response::HTTP_SEE_OTHER);
    }
}
