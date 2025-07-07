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
            // First time setup - give max boosters and start cooldown
            $user->setBoosterStack($this->maxBoosterStack);
            $user->setBoosterCreditUpdatedAt(new \DateTime());
        } else {
            // Calculate how much time has passed since last refresh
            $now = new \DateTime();
            $diffSeconds = $now->getTimestamp() - $lastRefreshed->getTimestamp();
            $cooldownSeconds = $this->boosterOpenCooldownHours * 3600; // Convert hours to seconds

            // Calculate total boosters that should be available based on time elapsed
            $totalBoostersEarned = floor($diffSeconds / $cooldownSeconds);
            
            if ($totalBoostersEarned > 0) {
                // Set stack to current + earned, capped at max
                $newStack = min($currentStack + $totalBoostersEarned, $this->maxBoosterStack);
                $user->setBoosterStack($newStack);
                
                // Reset the cooldown timer to now
                $user->setBoosterCreditUpdatedAt(new \DateTime());
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