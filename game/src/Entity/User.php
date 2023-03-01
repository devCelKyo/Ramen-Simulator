<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements \JsonSerializable
{
    const REBIRTH_PRICES = array(30000, 300000, 3000000, 30000000);

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $discord_id = null;

    #[ORM\Column]
    private ?int $money = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $last_daily_claim = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Restaurant::class, orphanRemoval: true)]
    private Collection $restaurants;

    #[ORM\Column]
    private ?int $rebirth = null;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
    }

    public function jsonSerialize(): mixed 
    {
        return [
            'discord_id' => $this->getDiscordId(),
            'money' => $this->getMoney()
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

    public function getMoney(): ?int
    {
        return $this->money;
    }

    public function setMoney(int $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function addMoney(int $money): self // NEVER EVER USE addMoney WITH NEGATIVE VALUES TO WITHDRAW MONEY, IT USES THE REBIRTH MULTIPLIER !!!!!
    {
        $this->money = $this->money + $this->getRebirthMultiplier()*$money;

        return $this;
    }

    public function withdrawMoney(int $money): self // USE THIS INSTEAD TO WITHDRAW MONEY, READ THE NAME
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

    public function getRebirth(): ?int
    {
        return $this->rebirth;
    }

    public function setRebirth(int $rebirth): self
    {
        $this->rebirth = $rebirth;

        return $this;
    }

    public function getRebirthMultiplier(): int
    {
        if ($this->rebirth == null || $this->rebirth == 0) {
            return 1;
        }
        else {
            return pow(2, $this->rebirth);
        }
    }
}
