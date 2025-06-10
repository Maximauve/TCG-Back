<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use App\Repository\UserRepository;

class JWTDecodedListener
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();
        $user = $this->userRepository->findOneByUsername($payload['username']);

        $payload['user'] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'profilePicture' => $user->getProfilePicture(),
            'description' => $user->getDescription(),
        ];

        $event->setPayload($payload); // Don't forget to regive the payload for next event / step
    }
}