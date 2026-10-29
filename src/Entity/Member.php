<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[Vich\Uploadable()]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Indiquez le nom du membre.')]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[Vich\UploadableField(mapping: 'member', fileNameProperty: 'imageUrl')]
    #[Assert\Image()]
    private ?File $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true, message: 'Lien invalide : saisissez une adresse complète commençant par https://')]
    private ?string $xlink = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true, message: 'Lien invalide : saisissez une adresse complète commençant par https://')]
    private ?string $linkedinlink = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true, message: 'Lien invalide : saisissez une adresse complète commençant par https://')]
    private ?string $facebookLink = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): static
    {
        $this->image = $image;

        // Force Doctrine à détecter la modification pour que Vich enregistre le nouveau fichier
        if (null !== $image) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getXlink(): ?string
    {
        return $this->xlink;
    }

    public function setXlink(?string $xlink): static
    {
        $this->xlink = $xlink;

        return $this;
    }

    public function getLinkedinlink(): ?string
    {
        return $this->linkedinlink;
    }

    public function setLinkedinlink(?string $linkedinlink): static
    {
        $this->linkedinlink = $linkedinlink;

        return $this;
    }

    public function getFacebookLink(): ?string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): static
    {
        $this->facebookLink = $facebookLink;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
