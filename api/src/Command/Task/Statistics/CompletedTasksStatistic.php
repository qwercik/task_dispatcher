<?php

namespace App\Command\Task\Statistics;

class CompletedTasksStatistic extends AbstractTaskStatistic
{
    protected int $order = 40;
    protected string $title = 'Completed';
    protected string $repositoryMethod = 'countCompleted';
}
