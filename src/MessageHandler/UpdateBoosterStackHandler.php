<?php

namespace App\MessageHandler;

use App\Message\UpdateBoosterStackMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateBoosterStackHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly int $boosterOpenCooldownHours,
        private readonly int $maxBoosterStack,
    ) {
    }

    public function __invoke(UpdateBoosterStackMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());
        if (!$user) {
            return; // User not found, skip
        }

        $lastRefreshed = $user->getBoosterCreditUpdatedAt();
        $currentStack = $user->getBoosterStack();

        if ($lastRefreshed === null) {
            // First time setup
            $user->setBoosterStack($this->maxBoosterStack);
            $user->setBoosterCreditUpdatedAt(new \DateTime());
        } else {
            // Calculate earned boosters since last refresh
            $now = new \DateTime();
            $diffSeconds = $now->getTimestamp() - $lastRefreshed->getTimestamp();
            $cooldownSeconds = $this->boosterOpenCooldownHours * 3600;

            $earnedBoosters = 0;
            if ($cooldownSeconds > 0 && $diffSeconds > 0) {
                $earnedBoosters = floor($diffSeconds / $cooldownSeconds);
            }

            if ($earnedBoosters > 0) {
                $newStack = min($currentStack + $earnedBoosters, $this->maxBoosterStack);
                $user->setBoosterStack($newStack);

                // Move the refresh date forward by the number of intervals earned
                $newRefreshDate = new \DateTime('@' . $lastRefreshed->getTimestamp());
                $newRefreshDate->add(new \DateInterval('PT' . ($earnedBoosters * $this->boosterOpenCooldownHours) . 'H'));
                $user->setBoosterCreditUpdatedAt($newRefreshDate);
            }
        }

        $this->entityManager->flush();
    }
} 