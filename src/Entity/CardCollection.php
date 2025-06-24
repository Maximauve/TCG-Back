<?php

namespace App\Entity;

use App\Repository\CardCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardCollectionRepository::class)]
class CardCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $display_image = null;

    #[ORM\Column(length: 255)]
    private ?string $booster_image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $release_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $end_date = null;

    #[ORM\Column]
    private ?bool $is_special = null;

    #[ORM\ManyToOne(inversedBy: 'cardCollections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(targetEntity: Card::class, mappedBy: 'collection')]
    private Collection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDisplayImage(): ?string
    {
        return $this->display_image;
    }

    public function setDisplayImage(string $display_image): static
    {
        $this->display_image = $display_image;

        return $this;
    }

    public function getBoosterImage(): ?string
    {
        return $this->booster_image;
    }

    public function setBoosterImage(string $booster_image): static
    {
        $this->booster_image = $booster_image;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->release_date;
    }

    public function setReleaseDate(\DateTimeImmutable $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeImmutable $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function isSpecial(): ?bool
    {
        return $this->is_special;
    }

    public function setIsSpecial(bool $is_special): static
    {
        $this->is_special = $is_special;

        return $this;
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

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setCollection($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getCollection() === $this) {
                $card->setCollection(null);
            }
        }

        return $this;
    }
}
