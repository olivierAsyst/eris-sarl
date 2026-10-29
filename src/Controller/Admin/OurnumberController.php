<?php

namespace App\Controller\Admin;

use App\Entity\Ournumber;
use App\Form\OurnumberType;
use App\Repository\OurnumberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ournumber', name:"admin.numbers.")]
#[IsGranted('ROLE_USER')]
final class OurnumberController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(OurnumberRepository $ournumberRepository): Response
    {
        return $this->render('admin/ournumber/index.html.twig', [
            'ournumbers' => $ournumberRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ournumber = new Ournumber();
        $form = $this->createForm(OurnumberType::class, $ournumber);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ournumber);
            $entityManager->flush();

            $this->addFlash('success', 'Élément ajouté avec succès.');

            return $this->redirectToRoute('admin.numbers.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/ournumber/new.html.twig', [
            'ournumber' => $ournumber,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Ournumber $ournumber): Response
    {
        return $this->render('admin/ournumber/show.html.twig', [
            'ournumber' => $ournumber,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ournumber $ournumber, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OurnumberType::class, $ournumber);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Modifications enregistrées.');

            return $this->redirectToRoute('admin.numbers.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/ournumber/edit.html.twig', [
            'ournumber' => $ournumber,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Ournumber $ournumber, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ournumber->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ournumber);
            $entityManager->flush();
            $this->addFlash('success', 'Élément supprimé.');
        }

        return $this->redirectToRoute('admin.numbers.index', [], Response::HTTP_SEE_OTHER);
    }
}
