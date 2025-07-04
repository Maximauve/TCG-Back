<?php

namespace App\Entity;

use App\Repository\UserCardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserCardRepository::class)]
class UserCard
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'userCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $cardTemplate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $obtainedAt = null;

    #[ORM\Column(length: 50)]
    private ?string $obtainedFrom = null; // 'booster', 'trade', 'gift', etc.

    public function __construct()
    {
        $this->obtainedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCardTemplate(): ?Card
    {
        return $this->cardTemplate;
    }

    public function setCardTemplate(?Card $cardTemplate): static
    {
        $this->cardTemplate = $cardTemplate;

        return $this;
    }

    public function getObtainedAt(): ?\DateTimeImmutable
    {
        return $this->obtainedAt;
    }

    public function setObtainedAt(\DateTimeImmutable $obtainedAt): static
    {
        $this->obtainedAt = $obtainedAt;

        return $this;
    }

    public function getObtainedFrom(): ?string
    {
        return $this->obtainedFrom;
    }

    public function setObtainedFrom(string $obtainedFrom): static
    {
        $this->obtainedFrom = $obtainedFrom;

        return $this;
    }

    // Convenience methods to access card template properties
    public function getName(): ?string
    {
        return $this->cardTemplate?->getName();
    }

    public function getDescription(): ?string
    {
        return $this->cardTemplate?->getDescription();
    }

    public function getImage(): ?string
    {
        return $this->cardTemplate?->getImage();
    }

    public function getArtistTag(): ?string
    {
        return $this->cardTemplate?->getArtistTag();
    }

    public function getRarity(): ?CardRarity
    {
        return $this->cardTemplate?->getRarity();
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->cardTemplate?->getReleaseDate();
    }

    public function getDropRate(): ?float
    {
        return $this->cardTemplate?->getDropRate();
    }

    public function getCollection(): ?CardCollection
    {
        return $this->cardTemplate?->getCollection();
    }
} 