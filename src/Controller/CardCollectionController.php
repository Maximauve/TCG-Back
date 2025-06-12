<?php


namespace App\Controller;

use App\Repository\CardCollectionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CardCollectionController extends AbstractController
{
    // get user card collections
    #[Route('/api/card-collections', name: 'app_card_collections', methods: ['GET'])]
    public  function index(CardCollectionRepository $cardCollectionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $collections = $cardCollectionRepository->findBy(['owner' => $user]);

        $data = [];
        foreach ($collections as $collection) {
            $data[] = [
                'id' => $collection->getId(),
                'name' => $collection->getName(),
                'description' => $collection->getDescription(),
                'display_img' => $collection->getDisplayImage(),
            ];
        }

        return $this->json($data);
    }

    // get user card collection by id
    #[Route('/api/card-collections/{id}', name: 'app_card_collection', methods: ['GET'])]
    public function showCardCollection(int $id, CardCollectionRepository $cardCollectionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $collection = $cardCollectionRepository->findOneBy(['id' => $id, 'owner' => $user]);

        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        //get cards in the collection
        $cards = $collection->getCards()->map(function ($card) {
            return [
                'id' => $card->getId(),
                'name' => $card->getName(),
                'description' => $card->getDescription(),
                'image' => $card->getImage(),
                'artist' => $card->getArtistTag(),
            ];
        })->toArray();

        return $this->json([
            'id' => $collection->getId(),
            'name' => $collection->getName(),
            'description' => $collection->getDescription(),
            'display_img' => $collection->getDisplayImage(),
            'cards' => $cards,
        ]);
    }
}
