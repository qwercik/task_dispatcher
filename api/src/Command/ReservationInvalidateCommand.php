<?php

namespace App\Command;

use App\Entity\Result;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reservation:invalidate',
    description: 'Invalidate a reservation (remove all its results and make it free again)',
)]
class ReservationInvalidateCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('reservation', InputArgument::OPTIONAL, 'Reservation uuid');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $id = $input->getArgument('reservation');
        if (!UuidV4::isValid($id)) {
            $io->error("Value '$id' is not a valid UUID");
            return Command::FAILURE;
        }

        $task = $this->entityManager->getRepository(Task::class)->findOneById($id);
        if ($task === null) {
            $io->error("Reservation with id '$id' as not found");
            return Command::FAILURE;
        }

        $task->setReservedAt(null);
        $task->setFinishedAt(null);
        $this->entityManager->persist($task);
        $this->entityManager->getRepository(Result::class)->deleteAllByTask($task);
        $this->entityManager->flush();

        $io->success('Reservation invalidated successfully');
        return Command::SUCCESS;
    }
}
