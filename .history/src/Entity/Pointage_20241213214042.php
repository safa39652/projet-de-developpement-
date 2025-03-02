<?
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

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    // Getters et setters pour les nouvelles colonnes
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
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateMoisAnneeHeure(): void
    {
        if ($this->heureEntree) {
            $this->mois = (int) $this->heureEntree->format('m');
            $this->annee = (int) $this->heureEntree->format('Y');
            $this->heure = new \DateTime($this->heureEntree->format('H:i:s'));
    
            // Debugging log
            error_log("updateMoisAnneeHeure called: mois={$this->mois}, annee={$this->annee}, heure={$this->heure->format('H:i:s')}");
        }
    }
}