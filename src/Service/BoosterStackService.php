<?php

namespace App\Service;

use App\Entity\User;
use App\Message\UpdateBoosterStackMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BoosterStackService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly int $boosterOpenCooldownHours,
        private readonly int $maxBoosterStack,
    ) {
    }

    /**
     * Update booster stack for a user (synchronous)
     */
    public function updateBoosterStack(User $user): void
    {
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
            $cooldownSeconds = $this->boosterOpenCooldownHours * 3600; // Convert hours to seconds

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

    /**
     * Schedule booster stack update for a user (asynchronous)
     */
    public function scheduleBoosterStackUpdate(string $userId): void
    {
        $message = new UpdateBoosterStackMessage($userId);
        $this->messageBus->dispatch($message);
    }

    /**
     * Get time until next booster for a user
     */
    public function getTimeUntilNextBooster(User $user): ?\DateTime
    {
        $lastRefreshed = $user->getBoosterCreditUpdatedAt();
        
        if ($lastRefreshed === null || $user->getBoosterStack() >= $this->maxBoosterStack) {
            return null; // No waiting time needed
        }

        $nextBoosterAt = new \DateTime('@' . $lastRefreshed->getTimestamp());
        $nextBoosterAt->add(new \DateInterval('PT' . $this->boosterOpenCooldownHours . 'H'));
        
        return $nextBoosterAt;
    }

    /**
     * Get the maximum booster stack size
     */
    public function getMaxBoosterStack(): int
    {
        return $this->maxBoosterStack;
    }
} 