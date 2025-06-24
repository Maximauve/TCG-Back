<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Card;
use App\Entity\CardCollection;
use App\Entity\CardRarity;
use App\Entity\User;
use App\Entity\UserCard;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

final class CardController extends AbstractController
{
    // show user cards
    #[Route('/api/cards', name: 'app_cards', methods: ['GET'])]
    #[OA\Tag(name: 'Cards')]
    #[OA\Response(
        response: 200,
        description: 'Get user cards',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: UserCard::class))
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    public function showCards(): Response
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        $cards = $user->getUserCards()->map(fn($userCard) => $this->formatUserCardData($userCard))->toArray();

        return $this->json($cards);
    }

    #[Route('/api/cards/latest', name: 'app_cards_latest', methods: ['GET'])]
    #[OA\Tag(name: 'Cards')]
    #[OA\Response(
        response: 200,
        description: 'Get latest rare cards (rare or above) owned by user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: UserCard::class))
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    public function showLatestCards(): Response
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        $rareUserCards = $user->getUserCards()
            ->filter(function(UserCard $userCard) {
                $rarity = $userCard->getRarity();
                return $rarity !== CardRarity::COMMON && $rarity !== CardRarity::UNCOMMON;
            })
            ->toArray();

        usort($rareUserCards, function(UserCard $a, UserCard $b) {
            return $b->getReleaseDate() <=> $a->getReleaseDate();
        });

        $formattedCards = array_map(fn($userCard) => $this->formatUserCardData($userCard), $rareUserCards);

        return $this->json($formattedCards);
    }

    /**
     * Get authenticated user or return error response
     */
    private function getAuthenticatedUser(): User|Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return $user;
    }

    /**
     * Format UserCard data
     */
    private function formatUserCardData(UserCard $userCard): array
    {
        return [
            'id' => $userCard->getId(),
            'name' => $userCard->getName(),
            'description' => $userCard->getDescription(),
            'rarity' => $userCard->getRarity()?->value,
            'image' => $userCard->getImage(),
            'artist' => $userCard->getArtistTag(),
            'obtained_at' => $userCard->getObtainedAt()?->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s'),
            'obtained_from' => $userCard->getObtainedFrom(),
        ];
    }
}
