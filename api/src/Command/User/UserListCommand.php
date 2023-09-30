<?php

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
    name: 'app:user:list',
    description: 'List users',
)]
class UserListCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->renderTable($output);
        return Command::SUCCESS;
    }

    public function getTableRows(): array
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return array_map(fn($user) => [
            $user->getId(),
            $user->getName(),
            $user->getAuthToken(),   
        ], $users);
    }

    public function renderTable(OutputInterface $output)
    {
        $rows = $this->getTableRows();
        $table = new Table($output);
        $table
            ->setHeaders(['UUID', 'Name', 'Auth token'])
            ->setRows($rows)
        ;
        $table->render();
    }
}

