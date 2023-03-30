<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use App\Utils\Utils;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\Debug;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant implements \JsonSerializable
{
    // GAMEPLAY CONSTANTS FOR BALANCING

    const UPGRADE_PRICES = array("1000", "5000", "10000", "20000", "40000", "80000", "160000", "320000", "640000");
    const PRICE = "30000";
    const STORAGES = array("100", "500", "1000", "2000", "4000", "8000", "16000", "32000", "64000", "128000");
    const RAMEN_COST = 1;
    const RAMEN_VALUES = array(4, 6, 8, 10, 12, 14, 16, 18, 20, 22);
    const WORKERS_SPEED = 3; // Minutes per ramen per worker
    const WORKERS_COST = 100;
    
    const STAR_WORKERS_SPEED_COEF = 0.95;
    const STAR_UPGRADE_PRICES_COEF = 10;
    const STAR_STORAGE_COEF = 10;
    const STAR_RAMEN_VALUES_COEF = 10;

    public static function getPrice(): \GMP
    {
        return gmp_init(self::PRICE);
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'restaurants', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    private ?int $stars = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column]
    private ?int $quality = null;

    #[ORM\Column(type: Types::OBJECT, nullable: true)]
    private ?\GMP $ramen_stored = null;

    #[ORM\Column(type: Types::OBJECT, nullable: true)]
    private ?\GMP $workers = null;

    #[ORM\Column(length: 10)]
    private ?string $public_id = null;

    #[ORM\Column(type: Types::OBJECT, nullable: true)]
    private ?\GMP $money_cached = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $last_update = null;

    public function __construct()
    {
        $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $digits = "0123456789";

        $this->public_id = $letters[random_int(0, 25)] . $letters[random_int(0, 25)];
        $this->public_id .= $digits[random_int(0, 9)] .  $digits[random_int(0, 9)];
        $this->public_id .= $letters[random_int(0, 25)] . $letters[random_int(0, 25)];

        $this->capacity = 1;
        $this->quality = 1;
        $this->ramen_stored = gmp_init(0);
        $this->money_cached = gmp_init(0);
        $this->workers = gmp_init(0);
        $this->last_update = new \DateTime();
        $this->stars = 0;
    }

    public function jsonSerialize(): mixed 
    {
        return [
            'owner' => $this->owner->getDiscordId(),
            'public_id' => $this->public_id,
            'stars' => $this->getStars(),
            'capacity' => $this->capacity,
            'quality' => $this->quality,
            'ramen_stored' => Utils::gmpToString($this->ramen_stored),
            'max_storage' => Utils::gmpToString($this->getStorage()),
            'workers' => Utils::gmpToString($this->workers),
            'money_cached' => Utils::gmpToString($this->getMoneyCached()),
            'capacity_upgrade_price' => Utils::gmpToString($this->getUpgradeCapacityPrice()),
            'quality_upgrade_price' => Utils::gmpToString($this->getUpgradeQualityPrice())
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

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(?int $stars): self
    {
        $this->stars = $stars;

        return $this;
    }

    public function addStar(): self
    {
        $this->stars = $this->stars + 1;

        $this->setCapacity(1);
        $this->setQuality(1);
        $this->setWorkers(gmp_init(10));
        $this->setRamenStored($this->getStorage());

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

    public function getUpgradeCapacityPrice(): \GMP|string
    {
        if ($this->capacity == count(self::UPGRADE_PRICES) + 1) {
            return "MAXXED!";
        }
        return gmp_init(self::UPGRADE_PRICES[$this->getCapacity() - 1]) * gmp_init(pow(self::STAR_UPGRADE_PRICES_COEF, $this->getStars()));
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
        return self::RAMEN_VALUES[$this->quality - 1] * pow(self::STAR_RAMEN_VALUES_COEF, $this->getStars());
    }

    public function getRamenCost(): int
    {
        return self::RAMEN_COST;
    }

    public function getUpgradeQualityPrice(): \GMP|string
    {
        if ($this->quality == count(self::UPGRADE_PRICES) + 1) {
            return "MAXXED!";
        }
        return gmp_init(self::UPGRADE_PRICES[$this->getQuality() - 1]) * gmp_init(pow(self::STAR_UPGRADE_PRICES_COEF, $this->getStars()));
    }

    public function getRamenStored(): ?\GMP
    {
        return $this->ramen_stored;
    }

    public function setRamenStored(\GMP $ramen_stored): self
    {
        $this->ramen_stored = $ramen_stored;

        return $this;
    }

    public function addRamenStored(\GMP|int|string $ramen): self
    {
        $this->ramen_stored = $this->ramen_stored + $ramen;

        return $this;
    }

    public function sellRamen(\GMP|int|string $amount): self
    {
        $this->ramen_stored = $this->ramen_stored - $amount;
        $this->addMoneyCached($this->getRamenValue() * $amount);

        return $this;
    }

    public function getStorage(): \GMP
    {
        return gmp_init(self::STORAGES[$this->getCapacity() - 1]);
    }

    public function getWorkers(): ?\GMP
    {
        return $this->workers;
    }

    public function setWorkers(\GMP $workers): self
    {
        $this->workers = $workers;

        return $this;
    }

    public function addWorkers(\GMP|int|string $workers): self
    {
        $this->workers = $this->workers + $workers;

        return $this;
    }

    public function getWorkersSpeed(): int
    {
        return self::WORKERS_SPEED * pow(self::STAR_WORKERS_SPEED_COEF, $this->getStars());
    }

    public function getWorkersCost(): int
    {
        return self::WORKERS_COST;
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

    public function getMoneyCached(): ?\GMP
    {
        return $this->money_cached;
    }

    public function setMoneyCached(\GMP $money_cached): self
    {
        $this->money_cached = $money_cached;

        return $this;
    }

    public function addMoneyCached(\GMP|int|string $money): self
    {
        $this->money_cached = $this->money_cached + $money;

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
        if ($this->getRamenStored() > $this->getWorkers() * $steps) {
            $ramen_made = $this->getWorkers() * $steps;
        }
        else {
            $ramen_made = $this->getRamenStored();
        }
        $this->sellRamen($ramen_made);

        return $this;
    }

    public function claim(): \GMP // I am pretty sure you need to persist the owner in the database after calling claim... But who knows? Certainly not me!
    {
        $this->update();
        $owner = $this->getOwner();
        $given_money = $owner->addMoney($this->getMoneyCached());
        $this->setMoneyCached(gmp_init(0));

        return $given_money;
    }

    public function computeScore(): \GMP
    {
        $capacityScore = ($this->getCapacity() * ($this->getCapacity() + 1)) / 2;
        $qualityScore = ($this->getQuality() * ($this->getQuality() + 1)) / 2;
        $workersScore = gmp_div_q($this->getWorkers(), 50);

        return $capacityScore + $qualityScore + $workersScore;
    }
}
