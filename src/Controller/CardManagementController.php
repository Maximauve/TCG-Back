<?php

namespace App\Controller;

use App\DTO\CreateCardDTO;
use App\DTO\UpdateCardDTO;
use App\Entity\Card;
use App\Entity\CardCollection;
use App\Entity\CardRarity;
use App\Entity\User;
use App\Service\ImageUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

final class CardManagementController extends AbstractController
{
    #[Route('/api/manage/cards', name: 'app_admin_card_list', methods: ['GET'])]
    #[OA\Tag(name: 'Card Management')]
    #[OA\Response(
        response: 200,
        description: 'Card templates list',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Card::class))
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        $cards = $entityManager->getRepository(Card::class)->findAll();
        $formattedCards = array_map(fn($card) => $this->formatCardDataWithDetails($card), $cards);
        
        return $this->json($formattedCards);
    }

    #[Route('/api/manage/cards', name: 'app_admin_card_create', methods: ['POST'])]
    #[OA\Tag(name: 'Card Management')]
    #[OA\Response(response: 201, description: 'Card template created successfully')]
    #[OA\Response(response: 400, description: 'Bad request')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(ref: new Model(type: CreateCardDTO::class))
        )
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageUploaderService $imageUploader
    ): Response {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        $validationResult = $this->validateCreateRequest($request, $entityManager);
        if ($validationResult instanceof Response) {
            return $validationResult;
        }

        ['collection' => $collection, 'releaseDate' => $releaseDate] = $validationResult;

        $card = $this->createCardFromRequest($request, $collection, $releaseDate);

        $entityManager->persist($card);
        $entityManager->flush();

        return $this->json($this->formatCardDataWithDetails($card), Response::HTTP_CREATED);
    }

    #[Route('/api/manage/cards/{id}', name: 'app_admin_card_update', methods: ['PUT', 'POST'])]
    #[OA\Tag(name: 'Card Management')]
    #[OA\Response(response: 200, description: 'Card template updated successfully')]
    #[OA\Response(response: 400, description: 'Bad request')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 403, description: 'Forbidden')]
    #[OA\Response(response: 404, description: 'Card not found')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    #[OA\RequestBody(
        required: false,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(ref: new Model(type: UpdateCardDTO::class))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The ID of the card template',
    )]
    public function update(
        string $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ImageUploaderService $imageUploader
    ): Response {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        $card = $this->findCardWithOwnershipCheck($id, $user, $entityManager);
        if ($card instanceof Response) {
            return $card;
        }

        $updateResult = $this->updateCardFromRequest($card, $request, $imageUploader);
        if ($updateResult instanceof Response) {
            return $updateResult;
        }

        $entityManager->flush();

        return $this->json($this->formatCardDataWithDetails($card), Response::HTTP_OK);
    }

    #[Route('/api/manage/cards/{id}', name: 'app_admin_card_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Card Management')]
    #[OA\Response(response: 200, description: 'Card template deleted successfully')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 403, description: 'Forbidden')]
    #[OA\Response(response: 404, description: 'Card not found')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The ID of the card template',
    )]
    public function delete(
        string $id,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof Response) {
            return $user;
        }

        $card = $this->findCardWithOwnershipCheck($id, $user, $entityManager);
        if ($card instanceof Response) {
            return $card;
        }

        $entityManager->remove($card);
        $entityManager->flush();

        return $this->json(['message' => 'Card template deleted'], Response::HTTP_OK);
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
     * Find card and check ownership
     */
    private function findCardWithOwnershipCheck(string $id, User $user, EntityManagerInterface $entityManager): Card|Response
    {
        $card = $entityManager->getRepository(Card::class)->find($id);
        if (!$card) {
            return $this->json(['error' => 'Card not found'], Response::HTTP_NOT_FOUND);
        }

        if ($card->getCollection()->getOwner() !== $user) {
            return $this->json(['error' => 'You are not the owner of the collection containing this card'], Response::HTTP_FORBIDDEN);
        }

        return $card;
    }

    /**
     * Validate create request and return collection and release date
     */
    private function validateCreateRequest(Request $request, EntityManagerInterface $entityManager): array|Response
    {
        $requiredFields = ['name', 'description', 'artistTag', 'rarity', 'releaseDate', 'dropRate', 'collectionId'];
        $data = [];
        
        foreach ($requiredFields as $field) {
            $value = $request->request->get($field);
            if (!$value) {
                return $this->json(['error' => "Field '{$field}' is required"], Response::HTTP_BAD_REQUEST);
            }
            $data[$field] = $value;
        }

        $collection = $entityManager->getRepository(CardCollection::class)->find($data['collectionId']);
        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        $releaseDate = $this->parseDate($data['releaseDate']);
        if ($releaseDate instanceof Response) {
            return $releaseDate;
        }

        return ['collection' => $collection, 'releaseDate' => $releaseDate];
    }

    /**
     * Create card entity from request data
     */
    private function createCardFromRequest(Request $request, CardCollection $collection, \DateTimeImmutable $releaseDate): Card
    {
        $card = new Card();
        $card->setName($request->request->get('name'));
        $card->setDescription($request->request->get('description'));
        $card->setArtistTag($request->request->get('artistTag'));
        $card->setRarity(CardRarity::from($request->request->get('rarity')));
        $card->setReleaseDate($releaseDate);
        $card->setDropRate((float)$request->request->get('dropRate'));
        $card->setImage('CARD_PLACEHOLDER.png'); // Default image
        $card->setCollection($collection);

        return $card;
    }

    /**
     * Update card from request data
     */
    private function updateCardFromRequest(Card $card, Request $request, ImageUploaderService $imageUploader): ?Response
    {
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $artistTag = $request->request->get('artistTag');
        $rarity = $request->request->get('rarity');
        $releaseDate = $request->request->get('releaseDate');
        $dropRate = $request->request->get('dropRate');
        $image = $request->files->get('image');

        if ($name) $card->setName($name);
        if ($description) $card->setDescription($description);
        if ($artistTag) $card->setArtistTag($artistTag);
        
        if ($rarity) {
            try {
                $card->setRarity(CardRarity::from($rarity));
            } catch (\ValueError $e) {
                return $this->json(['error' => 'Invalid rarity value'], Response::HTTP_BAD_REQUEST);
            }
        }
        
        if ($releaseDate) {
            $releaseDateObj = $this->parseDate($releaseDate);
            if ($releaseDateObj instanceof Response) {
                return $releaseDateObj;
            }
            $card->setReleaseDate($releaseDateObj);
        }
        
        if ($dropRate !== null && $dropRate !== '') {
            $card->setDropRate((float)$dropRate);
        }
        
        if ($image instanceof UploadedFile) {
            $card->setImage($imageUploader->upload($image));
        }

        return null;
    }

    /**
     * Parse date string to DateTimeImmutable
     */
    private function parseDate(string $dateString): \DateTimeImmutable|Response
    {
        try {
            return new \DateTimeImmutable($dateString);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Format card data with detailed information
     */
    private function formatCardDataWithDetails(Card $card): array
    {
        return [
            'id' => $card->getId(),
            'name' => $card->getName(),
            'description' => $card->getDescription(),
            'image' => $card->getImage(),
            'artist' => $card->getArtistTag(),
            'rarity' => $card->getRarity()->value,
            'release_date' => $card->getReleaseDate()->format('Y-m-d H:i:s'),
            'drop_rate' => $card->getDropRate(),
            'collection_id' => $card->getCollection()->getId(),
        ];
    }
} 