<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
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

    public function jsonSerialize() {
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

    public function addMoney(int $money): self
    {
        $this->money = $this->money + $money;

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
}
