<?php

namespace App\Command\Task\Statistics;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractTaskStatistic implements TaskStatisticInterface
{
    protected int $order;
    protected string $title;
    protected string $repositoryMethod;

    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {}

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFormattedValue(): string
    {
        $method = $this->repositoryMethod;
        $value = $this->entityManager->getRepository(Task::class)->$method();
        return (string)($value ?? 'NONE');
    }
}
