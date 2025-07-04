<?php

namespace App\Controller;

use App\DTO\UserRegisterDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Nelmio\ApiDocBundle\Attribute\Model;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use OpenApi\Attributes as OA;

#[WithMonologChannel('exception')]
final class RegisterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    #[OA\Tag(name: 'Authentication')]
    #[OA\Response(response: 201, description: 'User registered successfully')]
    #[OA\Response(response: 400, description: 'Bad request')]
    #[OA\Response(response: 500, description: 'Internal server error')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: UserRegisterDTO::class))
    )]
    public function index(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response
    {
        try {
            /** @var UserRegisterDTO $userDTO */
            $userDTO = $serializer->deserialize(
                $request->getContent(),
                UserRegisterDTO::class,
                'json'
            );

            $errors = $validator->validate($userDTO);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            if ($userRepository->findOneBy(['email' => $userDTO->getEmail()])) {
                return $this->json(['error' => $translator->trans('user.already_exists')], Response::HTTP_BAD_REQUEST);
            }

            if ($userRepository->findOneBy(['username' => $userDTO->getUsername()])) {
                return $this->json(['error' => $translator->trans('user.username_taken')], Response::HTTP_BAD_REQUEST);
            }

            $user = new User();
            $user->setEmail($userDTO->getEmail());
            $user->setPassword($this->passwordHasher->hashPassword($user, $userDTO->getPassword()));
            $user->setFirstName($userDTO->getFirstName());
            $user->setLastName($userDTO->getLastName());
            $user->setDescription("");
            $user->setUsername($userDTO->getUsername());
            $user->setProfilePicture("https://api.dicebear.com/7.x/avataaars/svg?seed=" . $userDTO->getUsername());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json([
                'token' => $JWTManager->create($user),
                'message' => $translator->trans('user.registered_successfully')
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), [
                'at' => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json([
                'error' => $translator->trans('unexpected_error'),
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
