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
        $this->addOption('force', 'f', null, 'Force import already existing tasks. Warning: it removes all results of overwritten!');
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

        $total = count($jsons);
        $successCount = 0;
        foreach ($jsons as $json) {
            // TODO: powinien być jakiś deserializer (?) który zmapuje to na encję
            if (empty($json['key'])) {
                $io->error('Each task have to have a "key" property');
                continue;
            }

            $task = $this->entityManager->getRepository(Task::class)->findOneByKey($json['key']);
            if ($task !== null) {
                if (!$input->getOption('force')) {
                    $io->warning(sprintf('Task with key "%s" already exists. Use --force to overwrite', $json['key']));
                    continue;
                }

                $this->entityManager->remove($task);
                $this->entityManager->flush();
            }

            $task = new Task();
            $task->setKey($json['key']);
            $task->setParams($json['params']);
            $this->entityManager->persist($task);
            $successCount++;
        }

        $this->entityManager->flush();
        $io->success("{$successCount}/{$total} tasks imported successfully");
        return Command::SUCCESS;
    }
}
