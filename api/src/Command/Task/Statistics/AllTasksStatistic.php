<?php

namespace App\Command\Task\Statistics;

class AllTasksStatistic extends AbstractTaskStatistic
{
    protected int $order = 10;
    protected string $title = 'All tasks';
    protected string $repositoryMethod = 'countAll';
}
