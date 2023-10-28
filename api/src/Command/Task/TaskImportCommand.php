<?php

namespace App\Command\Task;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:import',
    description: 'Import tasks from JSONL',
)]
class TaskImportCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'JSONL file (pass - to read from stdin)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getArgument('file');
        if ($filename === '-') {
            $filename = 'php://stdin';
        }
        if (empty($filename)) {
            $io->error('Path cannot be empty. It should be either `-` (to read from stdin) or path to real file');
            return Command::FAILURE;
        }

        $jsons = array_map(
            fn($line) => json_decode(trim($line), true),
            explode(PHP_EOL, trim(file_get_contents($filename)))
        );
        foreach ($jsons as $json) {
            $task = new Task();
            $task->setData($json);
            $this->entityManager->persist($task);
        }
        $this->entityManager->flush();

        $io->success('Tasks imported successfully');
        return Command::SUCCESS;
    }
}
