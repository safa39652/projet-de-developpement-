<?php

namespace App\Entity;

use App\Repository\PointageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointageRepository::class)]
class Pointage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $employe = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureEntree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureSortie = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmploye(): ?string
    {
        return $this->employe;
    }

    public function setEmploye(string $employe): static
    {
        $this->employe = $employe;

        return $this;
    }

    public function getHeureEntree(): ?\DateTimeInterface
    {
        return $this->heureEntree;
    }

    public function setHeureEntree(\DateTimeInterface $heureEntree): static
    {
        $this->heureEntree = $heureEntree;

        return $this;
    }

    public function getHeureSortie(): ?\DateTimeInterface
    {
        return $this->heureSortie;
    }

    public function setHeureSortie(\DateTimeInterface $heureSortie): static
    {
        $this->heureSortie = $heureSortie;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
