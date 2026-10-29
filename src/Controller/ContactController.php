<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactType;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    /** Nombre maximum de messages par adresse IP et par heure (anti-spam). */
    private const MAX_PER_HOUR = 5;

    /** Délai minimum (secondes) entre l'affichage et l'envoi : en dessous, c'est un robot. */
    private const MIN_FILL_SECONDS = 3;

    public function __construct(
        #[Autowire('%app.contact_email%')] private readonly string $contactEmail,
        #[Autowire('%app.mailer_from%')] private readonly string $mailerFrom,
    ) {
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        ContactMessageRepository $repository,
        MailerInterface $mailer,
        LoggerInterface $logger,
    ): Response {
        $contact = new ContactMessage();

        // Pré-sélection du type de demande via ?sujet=devis|partenariat|candidature|information
        $sujet = $request->query->getString('sujet');
        if (isset(ContactMessage::SUBJECTS[$sujet])) {
            $contact->setSubjectType($sujet);
        }
        // Pré-remplissage du message quand on vient d'un service ou d'un projet
        if ($ref = trim($request->query->getString('objet'))) {
            $contact->setMessage(sprintf("Bonjour,\n\nJe souhaite des informations concernant : %s.\n\n", mb_substr($ref, 0, 150)));
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        $session = $request->getSession();

        if ($form->isSubmitted()) {
            $isBot = '' !== (string) $form->get('website')->getData()
                || (time() - (int) $session->get('contact_form_ts', 0)) < self::MIN_FILL_SECONDS;

            $ip = $request->getClientIp();
            if ($ip && $repository->countRecentFromIp($ip, new \DateTimeImmutable('-1 hour')) >= self::MAX_PER_HOUR) {
                $form->addError(new FormError('Vous avez envoyé plusieurs messages récemment. Merci de réessayer plus tard ou de nous appeler directement.'));
            }

            if ($isBot) {
                // On fait comme si tout s'était bien passé, sans rien enregistrer
                $this->addFlash('success', 'Merci, votre message a bien été envoyé.');

                return $this->redirectToRoute('app_contact', ['_fragment' => 'contact-form']);
            }

            if ($form->isValid()) {
                $contact->setIp($ip);
                // 1. Sauvegarde en base : la demande n'est jamais perdue
                $em->persist($contact);
                $em->flush();

                // 2. Notification à l'équipe + accusé de réception au visiteur
                try {
                    $mailer->send((new TemplatedEmail())
                        ->from(new Address($this->mailerFrom, 'Site ERIS SARL'))
                        ->to($this->contactEmail)
                        ->replyTo(new Address($contact->getEmail(), $contact->getFullName()))
                        ->subject(sprintf('[Site web] %s – %s', $contact->getSubjectLabel(), $contact->getFullName()))
                        ->htmlTemplate('emails/contact_notification.html.twig')
                        ->textTemplate('emails/contact_notification.txt.twig')
                        ->context(['contact' => $contact]));

                    $mailer->send((new TemplatedEmail())
                        ->from(new Address($this->mailerFrom, 'ERIS SARL'))
                        ->to(new Address($contact->getEmail(), $contact->getFullName()))
                        ->replyTo($this->contactEmail)
                        ->subject('Nous avons bien reçu votre message – ERIS SARL')
                        ->htmlTemplate('emails/contact_confirmation.html.twig')
                        ->textTemplate('emails/contact_confirmation.txt.twig')
                        ->context(['contact' => $contact]));

                    $contact->setMailSent(true);
                    $em->flush();
                } catch (\Throwable $e) {
                    // Le message est en base et visible dans l'admin : on ne bloque pas le visiteur
                    $logger->error('Envoi e-mail du formulaire de contact impossible', ['exception' => $e, 'contact_id' => $contact->getId()]);
                }

                $session->remove('contact_form_ts');
                $this->addFlash('success', 'Merci ! Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais (en général sous 48 h ouvrées).');

                // Redirection (pattern POST/Redirect/GET) : un rafraîchissement ne renvoie pas le formulaire
                return $this->redirectToRoute('app_contact', ['_fragment' => 'contact-form'], Response::HTTP_SEE_OTHER);
            }
        }

        $session->set('contact_form_ts', time());

        return $this->render('contact/index.html.twig', [
            'form' => $form,
            'contact_email' => $this->contactEmail,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }
}
