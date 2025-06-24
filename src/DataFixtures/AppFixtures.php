<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\CardCollection;
use App\Entity\CardRarity;
use App\Entity\User;
use App\Entity\UserCard;
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
        $card1->setName('Forest Guardian');
        $card1->setDescription('A mystical protector of ancient woodlands.');
        $card1->setImage('images/CARD_PLACEHOLDER.png');
        $card1->setArtistTag('artist1');
        $card1->setRarity(CardRarity::COMMON);
        $card1->setReleaseDate(new \DateTimeImmutable());
        $card1->setDropRate(0.7);
        $card1->setCollection($cardCollection);
        $manager->persist($card1);

        $card2 = new Card();
        $card2->setName('Thunder Raptor');
        $card2->setDescription('A lightning-fast bird that creates sonic booms.');
        $card2->setImage('images/CARD_PLACEHOLDER.png');
        $card2->setArtistTag('artist2');
        $card2->setRarity(CardRarity::UNCOMMON);
        $card2->setReleaseDate(new \DateTimeImmutable());
        $card2->setDropRate(0.3);
        $card2->setCollection($cardCollection);
        $manager->persist($card2);

        $card3 = new Card();
        $card3->setName('Abyssal Horror');
        $card3->setDescription('Eldritch terror from the deepest voids.');
        $card3->setImage('images/CARD_PLACEHOLDER.png');
        $card3->setArtistTag('artist3');
        $card3->setRarity(CardRarity::RARE);
        $card3->setReleaseDate(new \DateTimeImmutable());
        $card3->setDropRate(0.1);
        $card3->setCollection($cardCollection);
        $manager->persist($card3);

        $card4 = new Card();
        $card4->setName('Crystal Golem');
        $card4->setDescription('Living mineral formation that reflects magic.');
        $card4->setImage('images/CARD_PLACEHOLDER.png');
        $card4->setArtistTag('artist1');
        $card4->setRarity(CardRarity::COMMON);
        $card4->setReleaseDate(new \DateTimeImmutable());
        $card4->setDropRate(0.65);
        $card4->setCollection($cardCollection);
        $manager->persist($card4);

        $card5 = new Card();
        $card5->setName('Celestial Seraph');
        $card5->setDescription('Divine being of pure cosmic energy.');
        $card5->setImage('images/CARD_PLACEHOLDER.png');
        $card5->setArtistTag('artist2');
        $card5->setRarity(CardRarity::EPIC);
        $card5->setReleaseDate(new \DateTimeImmutable());
        $card5->setDropRate(0.05);
        $card5->setCollection($cardCollection);
        $manager->persist($card5);

        $card6 = new Card();
        $card6->setName('Inferno Titan');
        $card6->setDescription('Mountain-sized embodiment of volcanic fury.');
        $card6->setImage('images/CARD_PLACEHOLDER.png');
        $card6->setArtistTag('artist3');
        $card6->setRarity(CardRarity::MYTHIC);
        $card6->setReleaseDate(new \DateTimeImmutable());
        $card6->setDropRate(0.01);
        $card6->setCollection($cardCollection);
        $manager->persist($card6);

        $card7 = new Card();
        $card7->setName('Venomous Shade');
        $card7->setDescription('Spectral assassin that poisons with touch.');
        $card7->setImage('images/CARD_PLACEHOLDER.png');
        $card7->setArtistTag('artist1');
        $card7->setRarity(CardRarity::UNCOMMON);
        $card7->setReleaseDate(new \DateTimeImmutable());
        $card7->setDropRate(0.25);
        $card7->setCollection($cardCollection);
        $manager->persist($card7);

        $card8 = new Card();
        $card8->setName('Tundra Wolf');
        $card8->setDescription('Alpha predator of frozen wastelands.');
        $card8->setImage('images/CARD_PLACEHOLDER.png');
        $card8->setArtistTag('artist2');
        $card8->setRarity(CardRarity::COMMON);
        $card8->setReleaseDate(new \DateTimeImmutable());
        $card8->setDropRate(0.75);
        $card8->setCollection($cardCollection);
        $manager->persist($card8);

        $card9 = new Card();
        $card9->setName('Chrono Mage');
        $card9->setDescription('Manipulator of time and temporal forces.');
        $card9->setImage('images/CARD_PLACEHOLDER.png');
        $card9->setArtistTag('artist3');
        $card9->setRarity(CardRarity::RARE);
        $card9->setReleaseDate(new \DateTimeImmutable());
        $card9->setDropRate(0.12);
        $card9->setCollection($cardCollection);
        $manager->persist($card9);

        $card10 = new Card();
        $card10->setName('Spectral Pirate');
        $card10->setDescription('Ghostly raider of the ethereal seas.');
        $card10->setImage('images/CARD_PLACEHOLDER.png');
        $card10->setArtistTag('artist1');
        $card10->setRarity(CardRarity::UNCOMMON);
        $card10->setReleaseDate(new \DateTimeImmutable());
        $card10->setDropRate(0.35);
        $card10->setCollection($cardCollection);
        $manager->persist($card10);

        $card11 = new Card();
        $card11->setName('Ironwood Treant');
        $card11->setDescription('Ancient tree-being with bark like steel.');
        $card11->setImage('images/CARD_PLACEHOLDER.png');
        $card11->setArtistTag('artist2');
        $card11->setRarity(CardRarity::COMMON);
        $card11->setReleaseDate(new \DateTimeImmutable());
        $card11->setDropRate(0.6);
        $card11->setCollection($cardCollection);
        $manager->persist($card11);

        $card12 = new Card();
        $card12->setName('Anniversary Emblem');
        $card12->setDescription('Commemorative card for the game\'s anniversary.');
        $card12->setImage('images/CARD_PLACEHOLDER.png');
        $card12->setArtistTag('artist3');
        $card12->setRarity(CardRarity::SPECIAL);
        $card12->setReleaseDate(new \DateTimeImmutable());
        $card12->setDropRate(0.005);
        $card12->setCollection($cardCollection);
        $manager->persist($card12);

        $card13 = new Card();
        $card13->setName('Convention Exclusive');
        $card13->setDescription('Rare promotional card from gaming conventions.');
        $card13->setImage('images/CARD_PLACEHOLDER.png');
        $card13->setArtistTag('artist1');
        $card13->setRarity(CardRarity::PROMO);
        $card13->setReleaseDate(new \DateTimeImmutable());
        $card13->setDropRate(0.001);
        $card13->setCollection($cardCollection);
        $manager->persist($card13);

        $card14 = new Card();
        $card14->setName('First Edition Mark');
        $card14->setDescription('Limited first printing of a foundational card.');
        $card14->setImage('images/CARD_PLACEHOLDER.png');
        $card14->setArtistTag('artist2');
        $card14->setRarity(CardRarity::LIMITED);
        $card14->setReleaseDate(new \DateTimeImmutable());
        $card14->setDropRate(0.0001);
        $card14->setCollection($cardCollection);
        $manager->persist($card14);

        $card15 = new Card();
        $card15->setName('Desert Nomad');
        $card15->setDescription('Survivor who thrives in arid wastelands.');
        $card15->setImage('images/CARD_PLACEHOLDER.png');
        $card15->setArtistTag('artist3');
        $card15->setRarity(CardRarity::COMMON);
        $card15->setReleaseDate(new \DateTimeImmutable());
        $card15->setDropRate(0.55);
        $card15->setCollection($cardCollection);
        $manager->persist($card15);

        $card16 = new Card();
        $card16->setName('Plasma Elemental');
        $card16->setDescription('Living embodiment of raw electrical energy.');
        $card16->setImage('images/CARD_PLACEHOLDER.png');
        $card16->setArtistTag('artist1');
        $card16->setRarity(CardRarity::UNCOMMON);
        $card16->setReleaseDate(new \DateTimeImmutable());
        $card16->setDropRate(0.28);
        $card16->setCollection($cardCollection);
        $manager->persist($card16);

        $card17 = new Card();
        $card17->setName('Mercenary Captain');
        $card17->setDescription('Battle-hardened leader of sellswords.');
        $card17->setImage('images/CARD_PLACEHOLDER.png');
        $card17->setArtistTag('artist2');
        $card17->setRarity(CardRarity::COMMON);
        $card17->setReleaseDate(new \DateTimeImmutable());
        $card17->setDropRate(0.8);
        $card17->setCollection($cardCollection);
        $manager->persist($card17);

        $card18 = new Card();
        $card18->setName('Necropolis Lord');
        $card18->setDescription('Undead ruler of a city of bones.');
        $card18->setImage('images/CARD_PLACEHOLDER.png');
        $card18->setArtistTag('artist3');
        $card18->setRarity(CardRarity::RARE);
        $card18->setReleaseDate(new \DateTimeImmutable());
        $card18->setDropRate(0.08);
        $card18->setCollection($cardCollection);
        $manager->persist($card18);

        $card19 = new Card();
        $card19->setName('Sky Fortress');
        $card19->setDescription('Floating bastion of aerial domination.');
        $card19->setImage('images/CARD_PLACEHOLDER.png');
        $card19->setArtistTag('artist1');
        $card19->setRarity(CardRarity::EPIC);
        $card19->setReleaseDate(new \DateTimeImmutable());
        $card19->setDropRate(0.04);
        $card19->setCollection($cardCollection);
        $manager->persist($card19);

        $card20 = new Card();
        $card20->setName('Goblin Alchemist');
        $card20->setDescription('Unstable experimenter with dangerous potions.');
        $card20->setImage('images/CARD_PLACEHOLDER.png');
        $card20->setArtistTag('artist2');
        $card20->setRarity(CardRarity::COMMON);
        $card20->setReleaseDate(new \DateTimeImmutable());
        $card20->setDropRate(0.5);
        $card20->setCollection($cardCollection);
        $manager->persist($card20);

        // Add UserCard instances to the user's collection for testing
        $userCard1 = new UserCard();
        $userCard1->setCardTemplate($card1);
        $userCard1->setOwner($user);
        $userCard1->setObtainedFrom('fixture');
        $manager->persist($userCard1);

        $userCard4 = new UserCard();
        $userCard4->setCardTemplate($card4);
        $userCard4->setOwner($user);
        $userCard4->setObtainedFrom('fixture');
        $manager->persist($userCard4);

        $userCard5 = new UserCard();
        $userCard5->setCardTemplate($card5);
        $userCard5->setOwner($user);
        $userCard5->setObtainedFrom('fixture');
        $manager->persist($userCard5);

        $manager->flush();
    }
} 