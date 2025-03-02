namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Emotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $emotion;

    #[ORM\Column(type: 'float')]
    private $confidence;

    #[ORM\Column(type: 'datetime')]
    private $timestamp;

    // Getter et Setter pour 'id'
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et Setter pour 'emotion'
    public function getEmotion(): ?string
    {
        return $this->emotion;
    }

    public function setEmotion(string $emotion): self
    {
        $this->emotion = $emotion;

        return $this;
    }

    // Getter et Setter pour 'confidence'
    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence): self
    {
        $this->confidence = $confidence;

        return $this;
    }

    // Getter et Setter pour 'timestamp'
    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
