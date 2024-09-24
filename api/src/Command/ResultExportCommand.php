<?php

namespace App\Command;

use App\Entity\Task;
use App\Service\MimeExtensionObtainer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:result:export',
    description: 'Export finished tasks results',
)]
class ResultExportCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected MimeExtensionObtainer $mimeExtensionObtainer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Path for exported data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputDirectory = rtrim($input->getArgument('path'), '/');
        if (is_dir($outputDirectory)) {
            throw new \InvalidArgumentException('Output directory already exists');
        }

        foreach ($this->entityManager->getRepository(Task::class)->findAllCompleted() as $task) {
            $path = $outputDirectory . '/' . $task->getKey();
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir(dirname($path), recursive: true);
            }

            $results = $task->getResults();
            $with_numbering = count($results) > 1;
            foreach ($results as $index => $result) {
                $extension = $this->mimeExtensionObtainer->getExtension($result->getMimeType()) ?? "";
                if (!empty($extension)) {
                    $extension = ".$extension";
                }

                $number_infix = $with_numbering
                    ? '.' . ($index + 1)
                    : '';

                $result_path = "$path$number_infix$extension"; 
                file_put_contents($result_path, $result->getContent());
            }
        }

        return Command::SUCCESS;
    }
}
