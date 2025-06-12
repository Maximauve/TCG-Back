<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\CardCollection;
use App\Entity\CardRarity;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create User
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setUsername('testuser');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setProfilePicture('default.png');
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            'password'
        ));
        $manager->persist($user);

        // Create Card Collection
        $cardCollection = new CardCollection();
        $cardCollection->setName('Initial Collection');
        $cardCollection->setDescription('The very first collection of cards.');
        $cardCollection->setDisplayImage('collection_display.png');
        $cardCollection->setBoosterImage('collection_booster.png');
        $cardCollection->setReleaseDate(new \DateTimeImmutable());
        $cardCollection->setEndDate((new \DateTimeImmutable())->add(new \DateInterval('P1Y')));
        $cardCollection->setIsSpecial(false);
        $cardCollection->setOwner($user);
        $manager->persist($cardCollection);

        // Create Cards
        $card1 = new Card();
        $card1->setName('Dragon\'s Fire');
        $card1->setDescription('A powerful dragon that breathes fire.');
        $card1->setImage('dragons_fire.png');
        $card1->setArtistTag('artist1');
        $card1->setRarity(CardRarity::RARE);
        $card1->setReleaseDate(new \DateTimeImmutable());
        $card1->setDropRate(0.1);
        $card1->setCollection($cardCollection);
        $manager->persist($card1);

        $card2 = new Card();
        $card2->setName('Goblin Scout');
        $card2->setDescription('A weak but fast goblin scout.');
        $card2->setImage('goblin_scout.png');
        $card2->setArtistTag('artist2');
        $card2->setRarity(CardRarity::COMMON);
        $card2->setReleaseDate(new \DateTimeImmutable());
        $card2->setDropRate(0.8);
        $card2->setCollection($cardCollection);
        $manager->persist($card2);

        $card3 = new Card();
        $card3->setName('Mythic Phoenix');
        $card3->setDescription('A legendary phoenix that rises from ashes.');
        $card3->setImage('mythic_phoenix.png');
        $card3->setArtistTag('artist1');
        $card3->setRarity(CardRarity::MYTHIC);
        $card3->setReleaseDate(new \DateTimeImmutable());
        $card3->setDropRate(0.01);
        $card3->setCollection($cardCollection);
        $manager->persist($card3);
        
        // Add one card to the user's collection for testing
        $user->addCard($card1);

        $manager->flush();
    }
} 