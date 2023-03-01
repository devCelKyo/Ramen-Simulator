<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant implements \JsonSerializable
{
    const UPGRADE_PRICES = array(1000, 5000, 10000, 20000, 40000, 80000, 160000, 320000, 640000);
    const PRICE = 30000;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'restaurants', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $owner = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column]
    private ?int $quality = null;

    #[ORM\Column]
    private ?int $ramen_stored = null;

    #[ORM\Column]
    private ?int $workers = null;

    #[ORM\Column(length: 10)]
    private ?string $public_id = null;

    public function __construct()
    {
        $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $digits = "0123456789";

        $this->public_id = $letters[random_int(0, 25)] . $letters[random_int(0, 25)];
        $this->public_id .= $digits[random_int(0, 9)] .  $digits[random_int(0, 9)];
        $this->public_id .= $letters[random_int(0, 25)] . $letters[random_int(0, 25)];
    }

    public function jsonSerialize(): mixed 
    {
        return [
            'owner' => $this->owner->getDiscordId(),
            'capacity' => $this->capacity,
            'quality' => $this->quality,
            'ramen_stored' => $this->ramen_stored,
            'workers' => $this->workers
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?user
    {
        return $this->owner;
    }

    public function setOwner(?user $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function upgradeCapacity(): self
    {
        $this->capacity = $this->capacity + 1;

        return $this;
    }

    public function getUpgradeCapacityPrice(): int
    {
        return self::UPGRADE_PRICES[$this->getCapacity() - 1];
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function upgradeQuality(): self
    {
        $this->quality = $this->quality + 1;

        return $this;
    }

    public function getUpgradeQualityPrice(): int
    {
        return self::UPGRADE_PRICES[$this->getQuality() - 1];
    }

    public function getRamenStored(): ?int
    {
        return $this->ramen_stored;
    }

    public function setRamenStored(int $ramen_stored): self
    {
        $this->ramen_stored = $ramen_stored;

        return $this;
    }

    public function getWorkers(): ?int
    {
        return $this->workers;
    }

    public function setWorkers(int $workers): self
    {
        $this->workers = $workers;

        return $this;
    }

    public function getPublicId(): ?string
    {
        return $this->public_id;
    }

    public function setPublicId(string $public_id): self
    {
        $this->public_id = $public_id;

        return $this;
    }
}
