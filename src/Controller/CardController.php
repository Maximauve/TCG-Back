<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Card;
use App\Entity\CardCollection;
use App\Entity\CardRarity;
use App\Service\ImageUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CardController extends AbstractController
{
    // show user cards
    #[Route('/api/cards', name: 'app_cards', methods: ['GET'])]
    public  function showCards(UserRepository $userRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $cards = $user->getCards()->map(function ($card) {
            return [
                'id' => $card->getId(),
                'name' => $card->getName(),
                'description' => $card->getDescription(),
                'image' => $card->getImage(),
                'artist' => $card->getArtistTag(),
            ];
        })->toArray();

        return $this->json($cards);
    }

    #[Route('/api/cards', name: 'app_card_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageUploaderService $imageUploader
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $artistTag = $request->request->get('artistTag');
        $rarity = $request->request->get('rarity');
        $releaseDate = $request->request->get('releaseDate');
        $dropRate = $request->request->get('dropRate');
        $collectionId = $request->request->get('collectionId');

        /** @var UploadedFile|null $image */
        $image = $request->files->get('image');

        if (!$name || !$description || !$artistTag || !$rarity || !$releaseDate || !$dropRate || !$collectionId || !$image) {
            return $this->json(['error' => 'All fields and image are required'], Response::HTTP_BAD_REQUEST);
        }

        $collection = $entityManager->getRepository(CardCollection::class)->find($collectionId);
        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $releaseDateObj = new \DateTimeImmutable($releaseDate);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $card = new Card();
        $card->setName($name);
        $card->setDescription($description);
        $card->setArtistTag($artistTag);
        $card->setRarity(CardRarity::from($rarity));
        $card->setReleaseDate($releaseDateObj);
        $card->setDropRate((float)$dropRate);
        $card->setImage($imageUploader->upload($image));
        $card->setCollection($collection);

        $entityManager->persist($card);
        $entityManager->flush();

        return $this->json([
            'id' => $card->getId(),
            'name' => $card->getName(),
            'description' => $card->getDescription(),
            'image' => $card->getImage(),
            'artist' => $card->getArtistTag(),
            'rarity' => $card->getRarity()->value,
            'release_date' => $card->getReleaseDate()->format('Y-m-d H:i:s'),
            'drop_rate' => $card->getDropRate(),
            'collection_id' => $collection->getId(),
        ], Response::HTTP_CREATED);
    }
}
