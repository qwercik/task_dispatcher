<?php

namespace App\Command\Task\Statistics;

class ReservedTasksStatistic extends AbstractTaskStatistic
{
    protected int $order = 30;
    protected string $title = 'Reserved tasks';
    protected string $repositoryMethod = 'countReserved';
}
