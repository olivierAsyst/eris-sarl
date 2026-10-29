<?php

namespace App\Entity;

use App\Repository\OurnumberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OurnumberRepository::class)]
class Ournumber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $projects = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $experts = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getProjects(): ?string
    {
        return $this->projects;
    }

    public function setProjects(?string $projects): static
    {
        $this->projects = $projects;

        return $this;
    }

    public function getExperts(): ?string
    {
        return $this->experts;
    }

    public function setExperts(?string $experts): static
    {
        $this->experts = $experts;

        return $this;
    }

    public function getPartner(): ?string
    {
        return $this->partner;
    }

    public function setPartner(?string $partner): static
    {
        $this->partner = $partner;

        return $this;
    }
}
