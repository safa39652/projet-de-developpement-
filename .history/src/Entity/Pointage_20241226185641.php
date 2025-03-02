<?php
namespace App\Entity;


use App\Repository\PointageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointageRepository::class)]
#[ORM\HasLifecycleCallbacks]
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureSortie = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $mois = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $annee = null;
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private ?string $jour = null;

    // Getters et setters
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

    public function setHeureSortie(?\DateTimeInterface $heureSortie): static
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

    public function getMois(): ?int
    {
        return $this->mois;
    }

    public function setMois(int $mois): static
    {
        $this->mois = $mois;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }
     // Getter et setter pour la photo
     public function getPhoto(): ?string
     {
         return $this->photo;
     }
 
     public function setPhoto(?string $photo): self
     {
         $this->photo = $photo;
         return $this;
     }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateMoisAnneeHeureJour(): void
    {
        if (!$this->heureEntree) {
            throw new \LogicException('Heure d\'entrée est obligatoire pour calculer mois, année, heure et jour.');
        }
    
        // Définir la locale en français
        setlocale(LC_TIME, 'fr_FR.UTF-8'); // Définir la locale pour le français
    
        // Format du mois, de l'année et de l'heure
        $this->mois = (int) $this->heureEntree->format('m');
        $this->annee = (int) $this->heureEntree->format('Y');
        $this->heure = new \DateTime($this->heureEntree->format('H:i:s'));
    
        // Enregistrer le jour en français
        $this->jour = strftime('%A', $this->heureEntree->getTimestamp()); // '%A' pour le jour complet (e.g., "Lundi")
    }
}
