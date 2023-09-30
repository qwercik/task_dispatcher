<?php

namespace App\Command\Task;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand(
    name: 'app:task:stats',
    description: 'Print statistics about tasks',
)]
class TaskStatsCommand extends Command
{
    protected array $statistics;
    
    public function __construct(
        protected EntityManagerInterface $entityManager,
        #[TaggedIterator('app.task.statistic')]
        iterable $statistics
    ) {
        parent::__construct();
        $this->initializeStatistics($statistics);
    }

    protected function initializeStatistics(iterable $statistics): void
    {
        $this->statistics = $statistics instanceof \Traversable
            ? iterator_to_array($statistics)
            : $statistics;

        usort($this->statistics, fn($left, $right) => $left->getOrder() <=> $right->getOrder());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders($this->getStatisticTitles());
        $table->setRows([
            $this->getStatisticValues()
        ]);
        $table->render();

        return Command::SUCCESS;
    }

    protected function getStatisticTitles(): array
    {
        return array_map(fn($statistic) => $statistic->getTitle(), $this->statistics);
    }

    protected function getStatisticValues(): array
    {
        return array_map(fn($statistic) => $statistic->getFormattedValue(), $this->statistics);
    }
}
