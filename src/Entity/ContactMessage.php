<?php

namespace App\Entity;

use App\Repository\ContactMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message envoyé depuis le formulaire de contact.
 * Il est enregistré en base AVANT l'envoi de l'e-mail : aucune demande n'est perdue
 * même si la messagerie est en panne.
 */
#[ORM\Entity(repositoryClass: ContactMessageRepository::class)]
#[ORM\Index(columns: ['created_at'])]
class ContactMessage
{
    public const SUBJECTS = [
        'devis' => 'Demande de devis',
        'information' => "Demande d'information",
        'partenariat' => 'Proposition de partenariat',
        'candidature' => 'Candidature / emploi',
        'autre' => 'Autre demande',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Choisissez le type de demande.')]
    #[Assert\Choice(choices: ['devis', 'information', 'partenariat', 'candidature', 'autre'])]
    private ?string $subjectType = 'information';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Indiquez votre nom.')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $fullName = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Indiquez votre adresse e-mail.')]
    #[Assert\Email(message: 'Cette adresse e-mail n\'est pas valide.', mode: 'strict')]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Assert\Regex(pattern: '/^[0-9+().\s-]{6,30}$/', message: 'Numéro de téléphone invalide.')]
    private ?string $phone = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $organization = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Écrivez votre message.')]
    #[Assert\Length(min: 10, max: 5000, minMessage: 'Votre message est trop court (10 caractères minimum).')]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ip = null;

    /** true si l'e-mail de notification est bien parti */
    #[ORM\Column(options: ['default' => false])]
    private bool $mailSent = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubjectType(): ?string
    {
        return $this->subjectType;
    }

    public function setSubjectType(?string $subjectType): static
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function getSubjectLabel(): string
    {
        return self::SUBJECTS[$this->subjectType] ?? 'Demande';
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): static
    {
        $this->fullName = null !== $fullName ? trim($fullName) : null;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = null !== $email ? trim($email) : null;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(?string $organization): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function isMailSent(): bool
    {
        return $this->mailSent;
    }

    public function setMailSent(bool $mailSent): static
    {
        $this->mailSent = $mailSent;

        return $this;
    }
}
