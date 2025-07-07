<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardCollection;
use App\Entity\User;
use App\Entity\UserCard;
use App\Service\BoosterStackService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoosterController extends AbstractController
{
    private const CARDS_PER_BOOSTER = 5;

    public function __construct(
        private readonly BoosterStackService $boosterStackService,
    ) {
    }

    #[Route('/api/boosters/open/{collectionId}', name: 'app_booster_open', methods: ['POST'])]
    #[OA\Tag(name: 'Boosters')]
    #[OA\Response(
        response: 200,
        description: 'Booster opened successfully',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                'message' => new OA\Property(property: 'message', type: 'string'),
                'cards' => new OA\Property(
                    property: 'cards',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: UserCard::class))
                ),
                'remaining_boosters' => new OA\Property(property: 'remaining_boosters', type: 'integer')
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Collection not found')]
    #[OA\Response(response: 400, description: 'No cards available in collection')]
    #[OA\Response(response: 429, description: 'Too many requests (cooldown)')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    #[OA\Parameter(
        name: 'collectionId',
        in: 'path',
        required: true,
        description: 'The ID of the collection to open a booster from',
    )]
    public function openBooster(
        string $collectionId,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        // Update user's booster stack
        $this->boosterStackService->updateBoosterStack($user);

        if ($user->getBoosterStack() > 0) {
            $collection = $this->findCollection($collectionId, $entityManager);
            if ($collection instanceof Response) {
                return $collection;
            }

            $availableCards = $collection->getCards()->toArray();
            if (empty($availableCards)) {
                return $this->json(['error' => 'No cards available in this collection'], Response::HTTP_BAD_REQUEST);
            }

            $drawnCards = $this->drawCards($availableCards, self::CARDS_PER_BOOSTER);

            // Decrement stack *before* creating cards and flushing
            $user->setBoosterStack($user->getBoosterStack() - 1);

            $userCards = $this->createUserCards($drawnCards, $user, $entityManager);

            return $this->json([
                'message' => 'Booster opened successfully!',
                'cards' => array_map(fn($userCard) => $this->formatUserCardData($userCard), $userCards),
                'remaining_boosters' => $user->getBoosterStack()
            ], Response::HTTP_OK);
        } else {
            // No boosters available
            $nextBoosterAt = $this->boosterStackService->getTimeUntilNextBooster($user);

            return $this->json([
                'error' => 'You have no booster packs. Check back later!',
                'next_booster_at' => $nextBoosterAt?->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s'),
                'current_stack' => $user->getBoosterStack()
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
    }

    #[Route('/api/boosters/status', name: 'app_booster_status', methods: ['GET'])]
    #[OA\Tag(name: 'Boosters')]
    #[OA\Response(
        response: 200,
        description: 'Get booster status',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                'current_stack' => new OA\Property(property: 'current_stack', type: 'integer'),
                'next_booster_at' => new OA\Property(property: 'next_booster_at', type: 'string', nullable: true),
                'can_open_booster' => new OA\Property(property: 'can_open_booster', type: 'boolean')
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getBoosterStatus(): Response
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        // Update user's booster stack
        $this->boosterStackService->updateBoosterStack($user);

        $nextBoosterAt = $this->boosterStackService->getTimeUntilNextBooster($user);

        return $this->json([
            'current_stack' => $user->getBoosterStack(),
            'next_booster_at' => $nextBoosterAt?->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s'),
            'can_open_booster' => $user->getBoosterStack() > 0
        ]);
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
     * Find collection by ID
     */
    private function findCollection(string $collectionId, EntityManagerInterface $entityManager): CardCollection|Response
    {
        $collection = $entityManager->getRepository(CardCollection::class)->find($collectionId);
        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }
        return $collection;
    }

    /**
     * Draw cards based on drop rates using weighted random selection
     */
    private function drawCards(array $availableCards, int $count): array
    {
        $drawnCards = [];
        
        // Precompute total weight of drop rates
        $totalWeight = array_sum(array_map(fn($card) => $card->getDropRate(), $availableCards));
        
        for ($i = 0; $i < $count; $i++) {
            $selectedCard = $this->selectRandomCardByDropRate($availableCards, $totalWeight);
            $drawnCards[] = $selectedCard;
        }
        
        return $drawnCards;
    }

    /**
     * Select a random card based on drop rates (weighted random)
     */
    private function selectRandomCardByDropRate(array $cards): Card
    {
        // Calculate total weight
        $totalWeight = array_reduce($cards, function($carry, Card $card) {
            return $carry + $card->getDropRate();
        }, 0);

        // Generate random number between 0 and total weight
        $random = mt_rand(0, (int)($totalWeight * 100)) / 100;

        // Find the selected card
        $currentWeight = 0;
        foreach ($cards as $card) {
            $currentWeight += $card->getDropRate();
            if ($random <= $currentWeight) {
                return $card;
            }
        }

        // Fallback to last card (should not happen)
        return end($cards);
    }

    /**
     * Create UserCard instances for drawn cards
     */
    private function createUserCards(array $drawnCards, User $user, EntityManagerInterface $entityManager): array
    {
        $userCards = [];
        
        foreach ($drawnCards as $card) {
            $userCard = new UserCard();
            $userCard->setCardTemplate($card);
            $userCard->setOwner($user);
            $userCard->setObtainedFrom('booster');
            
            $entityManager->persist($userCard);
            $userCards[] = $userCard;
        }
        
        $entityManager->flush();
        
        return $userCards;
    }

    /**
     * Format UserCard data for response
     */
    private function formatUserCardData(UserCard $userCard): array
    {
        return [
            'id' => $userCard->getId(),
            'name' => $userCard->getName(),
            'description' => $userCard->getDescription(),
            'image' => $userCard->getImage(),
            'artist' => $userCard->getArtistTag(),
            'rarity' => $userCard->getRarity()?->value,
            'release_date' => $userCard->getReleaseDate()?->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s'),
            'drop_rate' => $userCard->getDropRate(),
            'collection_id' => $userCard->getCollection()?->getId(),
            'obtained_at' => $userCard->getObtainedAt()?->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d H:i:s'),
            'obtained_from' => $userCard->getObtainedFrom(),
        ];
    }
} 