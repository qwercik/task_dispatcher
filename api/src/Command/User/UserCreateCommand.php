<?php

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create user',
)]
class UserCreateCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'User name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getOption('name');
        if (empty($name)) {
            $name = $io->askQuestion(new Question('User name'));
        }

        try {
            $user = new User();
            $user->setName($name);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success(implode(PHP_EOL, [
                "User name: " . $user->getName(),
                "Auth token: " . $user->getAuthToken(),
            ]));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Could not create user');
        }

        return Command::FAILURE;
    }
}

