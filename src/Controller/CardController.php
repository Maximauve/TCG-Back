<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
