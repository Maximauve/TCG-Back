<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\BoosterStackService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-booster-stacks',
    description: 'Update booster stacks for all users',
)]
class UpdateBoosterStacksCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly BoosterStackService $boosterStackService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();
        $updatedCount = 0;

        foreach ($users as $user) {
            $oldStack = $user->getBoosterStack();
            $this->boosterStackService->updateBoosterStack($user);
            $newStack = $user->getBoosterStack();

            if ($oldStack !== $newStack) {
                $updatedCount++;
                $io->writeln(sprintf(
                    'Updated user %s: %d -> %d boosters',
                    $user->getUsername(),
                    $oldStack,
                    $newStack
                ));
            }
        }

        $io->success(sprintf('Updated booster stacks for %d users.', $updatedCount));

        return Command::SUCCESS;
    }
} 