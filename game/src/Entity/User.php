<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Utils\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements \JsonSerializable
{
    const RESTAURANT_SLOT_PRICE = "30000";
    const STAR_RESTAURANT_SLOT_COEF = 100;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $discord_id = null;

    #[ORM\Column(type: Types::OBJECT, nullable: true)]
    private ?\GMP $money = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $last_daily_claim = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Restaurant::class, orphanRemoval: true)]
    private Collection $restaurants;

    #[ORM\Column(nullable: true)]
    private ?int $restaurant_slots = null;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
        $this->restaurant_slots = 1;
    }

    public function jsonSerialize(): mixed 
    {
        return [
            'discord_id' => $this->getDiscordId(),
            'money' => Utils::gmpToString($this->getMoney()),
            'restaurant_score' => $this->computeRestaurantScore(),
            'nb_restaurants' => $this->getRestaurants()->count(),
            'restaurant_slots' => $this->getRestaurantSlots(),
            'slot_price' => Utils::gmpToString($this->getRestaurantSlotsPrice())
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscordId(): ?string
    {
        return $this->discord_id;
    }

    public function setDiscordId(string $discord_id): self
    {
        $this->discord_id = $discord_id;

        return $this;
    }
    
    public function getMoney(): ?\GMP
    {
        return $this->money;
    }

    public function setMoney(\GMP $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function addMoney(\GMP|int|string $money): \GMP
    {
        $this->money = $this->money + $money;

        return $money;
    }

    public function withdrawMoney(\GMP|int|string $money): self
    {
        $this->money = $this->money - $money;

        return $this;
    }

    public function getLastDailyClaim(): ?\DateTimeInterface
    {
        return $this->last_daily_claim;
    }

    public function setLastDailyClaim(?\DateTimeInterface $last_daily_claim): self
    {
        $this->last_daily_claim = $last_daily_claim;

        return $this;
    }

    /**
     * @return Collection<int, Restaurant>
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): self
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants->add($restaurant);
            $restaurant->setOwner($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): self
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getOwner() === $this) {
                $restaurant->setOwner(null);
            }
        }

        return $this;
    }

    public function computeRestaurantScore(): \GMP
    {
        $score = gmp_init(0);
        $restaurants = $this->getRestaurants();
        foreach($restaurants as $restaurant) {
            $score = $score + $restaurant->computeScore();
        }

        return $score;
    }

    public function getRestaurantSlots(): ?int
    {
        return $this->restaurant_slots;
    }

    public function setRestaurantSlots(?int $restaurant_slots): self
    {
        $this->restaurant_slots = $restaurant_slots;

        return $this;
    }

    public function addRestaurantSlot(): self
    {
        $this->restaurant_slots = $this->restaurant_slots + 1;

        return $this;
    }

    public function getRestaurantSlotsPrice(): \GMP
    {
        return gmp_init(self::RESTAURANT_SLOT_PRICE) * pow(self::STAR_RESTAURANT_SLOT_COEF, $this->getRestaurantSlots() - 1);
    }
}
