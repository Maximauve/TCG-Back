<?php

namespace App\Controller;

use App\DTO\UserUpdateDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/api/user/{id}', name: 'app_user', methods: ['GET'])]
    public function get_user(
        UserRepository $userRepository,
        TranslatorInterface $translator,
        ?string $id = null
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => $translator->trans('user.not_found')], Response::HTTP_UNAUTHORIZED);
        }

        if ($id === null) {
            $user = $currentUser;
        } else {
            $user = $userRepository->find($id);
            if (!$user) {
                return $this->json(['error' => $translator->trans('user.not_found')], Response::HTTP_NOT_FOUND);
            }

            if (!in_array('ROLE_ADMIN', $currentUser->getRoles()) && $currentUser->getId() !== $user->getId()) {
                return $this->json(['error' => $translator->trans('user.unauthorized')], Response::HTTP_FORBIDDEN);
            }
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'profilePicture' => $user->getProfilePicture(),
            'description' => $user->getDescription(),
        ]);
    }

    #[Route('/api/user/{id}', name: 'app_user_update', methods: ['PUT'])]
    public function update_user(
        Request $request,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ?string $id = null
    ): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => $translator->trans('user.not_found')], Response::HTTP_UNAUTHORIZED);
        }

        // If no ID provided, use current user
        if ($id === null) {
            $user = $currentUser;
        } else {
            $user = $userRepository->find($id);
            if (!$user) {
                return $this->json(['error' => $translator->trans('user.not_found')], Response::HTTP_NOT_FOUND);
            }

            // Check if current user is admin or the same user
            if (!in_array('ROLE_ADMIN', $currentUser->getRoles()) && $currentUser->getId() !== $user->getId()) {
                return $this->json(['error' => $translator->trans('user.unauthorized')], Response::HTTP_FORBIDDEN);
            }
        }

        try {
            /** @var UserUpdateDTO $userDTO */
            $userDTO = $serializer->deserialize(
                $request->getContent(),
                UserUpdateDTO::class,
                'json'
            );
        } catch (\Exception $e) {
            return $this->json(['error' => $translator->trans('invalid_json_format')], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($userDTO);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        if ($userDTO->getEmail() && $userDTO->getEmail() !== $user->getEmail()) {
            if ($userRepository->findOneBy(['email' => $userDTO->getEmail()])) {
                return $this->json(['error' => $translator->trans('user.already_exists')], Response::HTTP_BAD_REQUEST);
            }
            $user->setEmail($userDTO->getEmail());
        }

        if ($userDTO->getUsername() && $userDTO->getUsername() !== $user->getUsername()) {
            if ($userRepository->findOneBy(['username' => $userDTO->getUsername()])) {
                return $this->json(['error' => $translator->trans('user.username_taken')], Response::HTTP_BAD_REQUEST);
            }
            $user->setUsername($userDTO->getUsername());
        }

        if ($userDTO->getPassword()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $userDTO->getPassword()));
        }

        if ($userDTO->getFirstName()) {
            $user->setFirstName($userDTO->getFirstName());
        }

        if ($userDTO->getLastName()) {
            $user->setLastName($userDTO->getLastName());
        }

        if ($userDTO->getDescription()) {
            $user->setDescription($userDTO->getDescription());
        }

        if ($userDTO->getProfilePicture()) {
            $user->setProfilePicture($userDTO->getProfilePicture());
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => $translator->trans('user.updated_successfully')
        ], Response::HTTP_OK);
    }

    #[Route('/api/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete_user(
        TranslatorInterface $translator,
        UserRepository $userRepository,
        ?string $id = null
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json(['error' => $translator->trans('user.not_found')], Response::HTTP_UNAUTHORIZED);
        }

        // If no ID provided, use current user
        if ($id === null) {
            $user = $currentUser;
        } else {
            $user = $userRepository->find($id);
            if (!$user) {
                return $this->json(['error' => $translator->trans('user.not_found')], Response::HTTP_NOT_FOUND);
            }

            // Check if current user is admin or the same user
            if (!in_array('ROLE_ADMIN', $currentUser->getRoles()) && $currentUser->getId() !== $user->getId()) {
                return $this->json(['error' => $translator->trans('user.unauthorized')], Response::HTTP_FORBIDDEN);
            }
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['message' => $translator->trans('user.deleted_successfully')], Response::HTTP_OK);
    }
}


