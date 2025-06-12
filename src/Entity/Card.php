<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
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
    private ?string $image = null;

    #[ORM\Column(length: 80)]
    private ?string $artist_tag = null;

    #[ORM\Column(length: 255)]
    private ?CardRarity $rarity = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $release_date = null;

    #[ORM\Column]
    private ?float $drop_rate = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'Cards')]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CardCollection $collection = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getArtistTag(): ?string
    {
        return $this->artist_tag;
    }

    public function setArtistTag(string $artist_tag): static
    {
        $this->artist_tag = $artist_tag;

        return $this;
    }

    public function getRarity(): ?CardRarity
    {
        return $this->rarity;
    }

    public function setRarity(CardRarity $rarity): static
    {
        $this->rarity = $rarity;

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

    public function getDropRate(): ?float
    {
        return $this->drop_rate;
    }

    public function setDropRate(float $drop_rate): static
    {
        $this->drop_rate = $drop_rate;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCard($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCard($this);
        }

        return $this;
    }

    public function getCollection(): ?CardCollection
    {
        return $this->collection;
    }

    public function setCollection(?CardCollection $collection): static
    {
        $this->collection = $collection;

        return $this;
    }
}
