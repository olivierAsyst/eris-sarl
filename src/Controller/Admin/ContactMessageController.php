<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** Messages reçus via le formulaire de contact (copie de sécurité des e-mails). */
#[Route('/admin/messages', name: 'admin.messages.')]
#[IsGranted('ROLE_USER')]
final class ContactMessageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ContactMessageRepository $repository): Response
    {
        return $this->render('admin/contact_message/index.html.twig', [
            'messages' => $repository->findLatest(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(ContactMessage $message, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($message);
            $em->flush();
            $this->addFlash('success', 'Message supprimé.');
        }

        return $this->redirectToRoute('admin.messages.index', [], Response::HTTP_SEE_OTHER);
    }
}
