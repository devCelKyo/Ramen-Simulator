<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\Debug;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant implements \JsonSerializable
{
    const UPGRADE_PRICES = array(1000, 5000, 10000, 20000, 40000, 80000, 160000, 320000, 640000);
    const PRICE = 30000;
    const STORAGES = array(100, 500, 1000, 2000, 4000, 8000, 16000, 32000, 64000);
    const RAMEN_COST = 0.2;
    const RAMEN_VALUES = array(2, 4, 8, 10, 13, 16, 19, 22, 25, 30);
    const WORKERS_SPEED = 3; // Minutes per ramen per worker

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'restaurants', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

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

    #[ORM\Column]
    private ?int $money_cached = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $last_update = null;

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
            'public_id' => $this->public_id,
            'capacity' => $this->capacity,
            'quality' => $this->quality,
            'ramen_stored' => $this->ramen_stored,
            'max_storage' => $this->getStorage(),
            'workers' => $this->workers,
            'money_cached' => $this->getMoneyCached()
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

    public function getRamenValue(): int
    {
        return self::RAMEN_VALUES[$this->quality - 1];
    }

    public function getRamenCost(): int
    {
        return self::RAMEN_COST;
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

    public function addRamenStored(int $ramen): self
    {
        $this->ramen_stored = $this->ramen_stored + $ramen;

        return $this;
    }

    public function sellRamen(int $amount): self
    {
        $this->ramen_stored = $this->ramen_stored - $amount;
        $this->addMoneyCached($this->getRamenValue() * $amount);

        return $this;
    }

    public function getStorage(): int
    {
        return self::STORAGES[$this->getCapacity() - 1];
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

    public function addWorkers(int $workers): self
    {
        $this->workers = $this->workers + $workers;

        return $this;
    }

    public function getWorkersSpeed(): int
    {
        return self::WORKERS_SPEED;
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

    public function getMoneyCached(): ?int
    {
        return $this->money_cached;
    }

    public function setMoneyCached(int $money_cached): self
    {
        $this->money_cached = $money_cached;

        return $this;
    }

    public function addMoneyCached(int $money): self
    {
        $this->money_cached = $this->money_cached + $money;

        return $this;
    }

    public function withdrawMoneyCached(int $money): self
    {
        $this->money_cached = max($this->money_cached - $money, 0);

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return clone $this->last_update;
    }

    public function setLastUpdate(?\DateTimeInterface $last_update): self
    {
        $this->last_update = clone $last_update;

        return $this;
    }

    public function update(): self
    {
        $now = new \DateTime();
        $last_update = $this->getLastUpdate();
        $difference = $last_update->diff($now); // This is a DateInterval, not a DateTime

        $delay = $this->getWorkersSpeed() * 60;
        // Let's find how many amount of $delay we can fit in $difference (integer division)
        $difference_seconds = date_create('@0')->add($difference)->getTimestamp();
        $steps = intdiv($difference_seconds, $delay);
        
        // We use an integer division, so the new update date isn't necessarily now() but the biggest multiple of $delay before now()
        $datetime = $steps * $this->getWorkersSpeed();
        $time_to_add = \DateInterval::createFromDateString($datetime." minutes");
        $last_update->add($time_to_add);
        $this->setLastUpdate($last_update);

        // Finally, let's compute how much ramen has been made and update accordingly
        $ramen_made = min($this->getRamenStored(), $this->getWorkers() * $steps);
        $this->sellRamen($ramen_made);

        return $this;
    }

    public function claim(): int // I am pretty sure you need to persist the owner in the database after calling claim... But who knows? Certainly not me!
    {
        $this->update();
        $owner = $this->getOwner();
        $given_money = $owner->addMoney($this->getMoneyCached());
        $this->setMoneyCached(0);

        return $given_money;
    }
}
