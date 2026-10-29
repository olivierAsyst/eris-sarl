<?php

namespace App\Controller\Admin;

use App\Entity\Services;
use App\Form\ServicesTypesType;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:"/services", name:"admin.services.")]
#[IsGranted('ROLE_USER')]
final class ServicesController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(ServicesRepository $repository): Response
    {
        $services = $repository->findAll();
        return $this->render('admin/services/index.html.twig', [
            'controller_name' => 'Les services',
            'services' => $services
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $en): Response
    {
        $service = new Services();
        
        $form = $this->createForm(ServicesTypesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $en->persist($service);
            $en->flush();

            $this->addFlash('success', 'Élément ajouté avec succès.');

            return $this->redirectToRoute('admin.services.index');
        }
        return $this->render('admin/services/create.html.twig', [
            'controller_name' => 'Ajouter un service',
            'form' => $form
        ]);
    }

    #[Route('/edit/{id}', name: 'update', methods: ['GET', 'POST'], requirements: ['id'=>Requirement::DIGITS])]
    public function edit(Services $service, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ServicesTypesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Modifications enregistrées.');

            return $this->redirectToRoute('admin.services.index');
        }
        return $this->render('admin/services/update.html.twig', [
            'controller_name' => 'Modifier un service',
            'service' => $service,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Services $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
            $this->addFlash('success', 'Élément supprimé.');
        }

        return $this->redirectToRoute('admin.services.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Services $service): Response
    {
        return $this->render('admin/services/show.html.twig', [
            'service' => $service,
        ]);
    }

}
