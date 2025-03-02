namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Emotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $employeName = null;  // Champ pour stocker le nom de l'employé

    #[ORM\Column(length: 255)]
    private ?string $emotion = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployeName(): ?string
    {
        return $this->employeName;
    }

    public function setEmployeName(string $employeName): static
    {
        $this->employeName = $employeName;

        return $this;
    }

    public function getEmotion(): ?string
    {
        return $this->emotion;
    }

    public function setEmotion(string $emotion): static
    {
        $this->emotion = $emotion;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
