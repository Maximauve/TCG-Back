<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: 'users')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    private ?string $profile_picture = null;

    #[ORM\Column(length: 120)]
    private ?string $username = null;

    /**
     * @var Collection<int, UserCard>
     */
    #[ORM\OneToMany(targetEntity: UserCard::class, mappedBy: 'owner')]
    private Collection $userCards;

    /**
     * @var Collection<int, CardCollection>
     */
    #[ORM\OneToMany(targetEntity: CardCollection::class, mappedBy: 'owner')]
    private Collection $cardCollections;

    /**
     * @var Collection<int, OAuthAccount>
     */
    #[ORM\OneToMany(targetEntity: OAuthAccount::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $linkedAccounts;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $boosterCreditUpdatedAt = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 1])]
    private int $boosterStack = 1;

    public function __construct()
    {
        $this->userCards = new ArrayCollection();
        $this->cardCollections = new ArrayCollection();
        $this->linkedAccounts = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profile_picture;
    }

    public function setProfilePicture(string $profile_picture): static
    {
        $this->profile_picture = $profile_picture;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, UserCard>
     */
    public function getUserCards(): Collection
    {
        return $this->userCards;
    }

    public function addUserCard(UserCard $userCard): static
    {
        if (!$this->userCards->contains($userCard)) {
            $this->userCards->add($userCard);
            $userCard->setOwner($this);
        }

        return $this;
    }

    public function removeUserCard(UserCard $userCard): static
    {
        if ($this->userCards->removeElement($userCard)) {
            // set the owning side to null (unless already changed)
            if ($userCard->getOwner() === $this) {
                $userCard->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        // Return card templates for backward compatibility
        return $this->userCards->map(fn($userCard) => $userCard->getCardTemplate());
    }

    /**
     * @return Collection<int, CardCollection>
     */
    public function getCardCollections(): Collection
    {
        return $this->cardCollections;
    }

    public function addCardCollection(CardCollection $cardCollection): static
    {
        if (!$this->cardCollections->contains($cardCollection)) {
            $this->cardCollections->add($cardCollection);
            $cardCollection->setOwner($this);
        }

        return $this;
    }

    public function removeCardCollection(CardCollection $cardCollection): static
    {
        if ($this->cardCollections->removeElement($cardCollection)) {
            // set the owning side to null (unless already changed)
            if ($cardCollection->getOwner() === $this) {
                $cardCollection->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OAuthAccount>
     */
    public function getLinkedAccounts(): Collection
    {
        return $this->linkedAccounts;
    }

    public function addLinkedAccount(OAuthAccount $linkedAccount): static
    {
        if (!$this->linkedAccounts->contains($linkedAccount)) {
            $this->linkedAccounts->add($linkedAccount);
            $linkedAccount->setUser($this);
        }

        return $this;
    }

    public function removeLinkedAccount(OAuthAccount $linkedAccount): static
    {
        if ($this->linkedAccounts->removeElement($linkedAccount)) {
            // set the owning side to null (unless already changed)
            if ($linkedAccount->getUser() === $this) {
                $linkedAccount->setUser(null);
            }
        }

        return $this;
    }

    public function getBoosterCreditUpdatedAt(): ?\DateTimeInterface
    {
        return $this->boosterCreditUpdatedAt;
    }

    public function setBoosterCreditUpdatedAt(?\DateTimeInterface $boosterCreditUpdatedAt): static
    {
        $this->boosterCreditUpdatedAt = $boosterCreditUpdatedAt;

        return $this;
    }

    public function getBoosterStack(): int
    {
        return $this->boosterStack;
    }

    public function setBoosterStack(int $boosterStack): static
    {
        $this->boosterStack = $boosterStack;

        return $this;
    }
}