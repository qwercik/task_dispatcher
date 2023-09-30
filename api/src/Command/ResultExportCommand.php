<?php

namespace App\Command;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'app:result:export',
    description: 'Export results to JSONL',
)]
class ResultExportCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $results = $this->entityManager->getRepository(Task::class)->findAllCompleted();
        foreach ($results as $result) {
            echo json_encode($result->getOutput()) . PHP_EOL;            
        }

        return Command::SUCCESS;
    }
}
