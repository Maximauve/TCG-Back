<?php

namespace App\Controller;

use App\DTO\OAuthDTO;
use App\DTO\UserRegisterDTO;
use App\Entity\OAuthAccount;
use App\Entity\User;
use App\Repository\OAuthAccountRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(format: 'json')]
final class AuthController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface    $JWTManager,
        private readonly UserRepository              $userRepository,
        private readonly OAuthAccountRepository      $oAuthAccountRepository,
        private readonly TranslatorInterface         $translator,
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function index(#[MapRequestPayload] UserRegisterDTO $userDTO): Response
    {
        if ($this->userRepository->findOneBy(['email' => $userDTO->getEmail()])) {
            return $this->json(['error' => $this->translator->trans('user.already_exists')], Response::HTTP_CONFLICT);
        }

        if ($this->userRepository->findOneBy(['username' => $userDTO->getUsername()])) {
            return $this->json(['error' => $this->translator->trans('user.username_taken')], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($userDTO->getEmail());
        $user->setPassword($this->passwordHasher->hashPassword($user, $userDTO->getPassword()));
        $user->setFirstName($userDTO->getFirstName());
        $user->setLastName($userDTO->getLastName());
        $user->setUsername($userDTO->getUsername());
        $user->setProfilePicture("https://api.dicebear.com/7.x/avataaars/svg?seed=JohnD");

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'token' => $this->JWTManager->create($user),
            'message' => $this->translator->trans('user.registered_successfully')
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/auth/oauth', name: 'api_oauth', methods: ['POST'])]
    public function handleOAuth(#[MapRequestPayload] OAuthDTO $authDTO): Response
    {
        $provider = $authDTO->provider;
        $accountId = $authDTO->providerId;

        $user = $this->getUser();
        if ($user !== null) {
            $oAuth = $this->oAuthAccountRepository->findOneBy([
                'provider' => $provider,
                'accountId' => $accountId,
                'user' => $user
            ]);
            if ($oAuth !== null) {
                return $this->json([
                    'token' => $this->JWTManager->create($user)
                ]);
            }
            $oAuth = new OAuthAccount();
            $oAuth->setProvider($provider);
            $oAuth->setAccountId($accountId);
            $user->addLinkedAccount($oAuth);

            try {
                $this->entityManager->persist($oAuth);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } catch (\Throwable $e) {
                return $this->json([
                    'error' => $e->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return $this->json([
                'token' => $this->JWTManager->create($user),
            ]);
        }

        $user = $this->userRepository->findByLinkedAccount($provider, $accountId);
        if (!empty($user)) {
            $user = $user[0];
            $this->JWTManager->create($user);
            return $this->json([
                'token' => $this->JWTManager->create($user)
            ]);
        }

        $user = $this->userRepository->findOneBy(['email' => $authDTO->email]);
        if ($user !== null) {
            return $this->json([
                'error' => $this->translator->trans('oauth.account_already_exists', ['%email%' => $authDTO->email]),
            ]);
        }
        $user = new User();
        $user->setEmail($authDTO->email);
        $user->setUsername($authDTO->name);
        $user->setFirstName($authDTO->firstName);
        $user->setLastName($authDTO->lastName);
        $user->setProfilePicture("https://api.dicebear.com/7.x/avataaars/svg?seed=JohnD");

        $oAuth = new OAuthAccount();
        $oAuth->setProvider($provider);
        $oAuth->setAccountId($accountId);
        $user->addLinkedAccount($oAuth);

        try {
            $this->entityManager->persist($user);
            $this->entityManager->persist($oAuth);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'token' => $this->JWTManager->create($user),
        ]);
    }
}
