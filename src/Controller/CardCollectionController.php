<?php

namespace App\Controller;

use App\DTO\CreateCardCollectionDTO;
use App\Entity\CardCollection;
use App\Entity\User;
use App\Repository\CardCollectionRepository;
use App\Repository\UserRepository;
use App\Service\ImageUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CardCollectionController extends AbstractController
{
    #[Route('/api/card-collections', name: 'app_card_collections', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Get user card collections',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CardCollection::class))
        ),
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    #[OA\Tag(name: 'Card Collection')]
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

    
    #[Route('/api/card-collections/{id}', name: 'app_card_collection', methods: ['GET'])]
    #[OA\Tag(name: 'Card Collection')]
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

    #[Route('/api/card-collections', name: 'app_card_collection_create', methods: ['POST'])]
    #[OA\Tag(name: 'Card Collection')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageUploaderService $imageUploader
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $releaseDate = $request->request->get('releaseDate');
        $endDate = $request->request->get('endDate');
        $isSpecial = filter_var($request->request->get('isSpecial'), FILTER_VALIDATE_BOOLEAN);

        /** @var UploadedFile|null $displayImage */
        $displayImage = $request->files->get('displayImage');
        /** @var UploadedFile|null $boosterImage */
        $boosterImage = $request->files->get('boosterImage');

        if (!$name || !$description || !$releaseDate || !$endDate || !$displayImage || !$boosterImage) {
            return $this->json(['error' => 'All fields and both images are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $releaseDateObj = new \DateTimeImmutable($releaseDate);
            $endDateObj = new \DateTimeImmutable($endDate);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $collection = new CardCollection();
        $collection->setName($name);
        $collection->setDescription($description);
        $collection->setDisplayImage($imageUploader->upload($displayImage));
        $collection->setBoosterImage($imageUploader->upload($boosterImage));
        $collection->setReleaseDate($releaseDateObj);
        $collection->setEndDate($endDateObj);
        $collection->setIsSpecial($isSpecial);
        $collection->setOwner($user);

        $entityManager->persist($collection);
        $entityManager->flush();

        return $this->json([
            'id' => $collection->getId(),
            'name' => $collection->getName(),
            'description' => $collection->getDescription(),
            'display_img' => $collection->getDisplayImage(),
            'booster_img' => $collection->getBoosterImage(),
            'release_date' => $collection->getReleaseDate()->format('Y-m-d H:i:s'),
            'end_date' => $collection->getEndDate()->format('Y-m-d H:i:s'),
            'is_special' => $collection->isSpecial(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/card-collections/update/{id}', name: 'app_card_collection_update', methods: ['POST'])]
    #[OA\Tag(name: 'Card Collection')]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ImageUploaderService $imageUploader
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $collection = $entityManager->getRepository(CardCollection::class)->find($id);
        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        if ($collection->getOwner() !== $user) {
            return $this->json(['error' => 'You are not the owner of this collection'], Response::HTTP_FORBIDDEN);
        }

        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $releaseDate = $request->request->get('releaseDate');
        $endDate = $request->request->get('endDate');
        $isSpecial = $request->request->has('isSpecial') ? filter_var($request->request->get('isSpecial'), FILTER_VALIDATE_BOOLEAN) : $collection->isSpecial();

        /** @var UploadedFile|null $displayImage */
        $displayImage = $request->files->get('displayImage');
        /** @var UploadedFile|null $boosterImage */
        $boosterImage = $request->files->get('boosterImage');

        if ($name) {
            $collection->setName($name);
        }
        if ($description) {
            $collection->setDescription($description);
        }
        if ($releaseDate) {
            try {
                $releaseDateObj = new \DateTimeImmutable($releaseDate);
                $collection->setReleaseDate($releaseDateObj);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid release date format'], Response::HTTP_BAD_REQUEST);
            }
        }
        if ($endDate) {
            try {
                $endDateObj = new \DateTimeImmutable($endDate);
                $collection->setEndDate($endDateObj);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid end date format'], Response::HTTP_BAD_REQUEST);
            }
        }
        if ($request->request->has('isSpecial')) {
            $collection->setIsSpecial($isSpecial);
        }
        if ($displayImage) {
            $collection->setDisplayImage($imageUploader->upload($displayImage));
        }
        if ($boosterImage) {
            $collection->setBoosterImage($imageUploader->upload($boosterImage));
        }

        $entityManager->flush();

        return $this->json([
            'id' => $collection->getId(),
            'name' => $collection->getName(),
            'description' => $collection->getDescription(),
            'display_img' => $collection->getDisplayImage(),
            'booster_img' => $collection->getBoosterImage(),
            'release_date' => $collection->getReleaseDate()->format('Y-m-d H:i:s'),
            'end_date' => $collection->getEndDate()->format('Y-m-d H:i:s'),
            'is_special' => $collection->isSpecial(),
        ], Response::HTTP_OK);
    }

    #[Route('/api/card-collections/{id}', name: 'app_card_collection_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Card Collection')]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        ImageUploaderService $imageUploader,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $collection = $entityManager->getRepository(CardCollection::class)->find($id);
        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        if ($collection->getOwner() !== $user) {
            return $this->json(['error' => 'You are not the owner of this collection'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($collection);
        $entityManager->flush();

        return $this->json(['message' => 'Collection deleted'], Response::HTTP_OK);
    }
}
