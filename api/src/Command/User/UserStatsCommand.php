<?php

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:stats',
    description: 'Print statistics about users',
)]
class UserStatsCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = $this->entityManager->getRepository(User::class)->findUsersWithCompletedTasksSummary();
        $rows = array_map(fn($row) => [
            $row['user']->getName(),
            $row['reserved_tasks_count'],
            $row['completed_tasks_count'],
        ], $rows);

        $table = new Table($output);
        $table->setHeaders(['User', 'Reserved tasks', 'Completed tasks']);
        $table->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }
}
