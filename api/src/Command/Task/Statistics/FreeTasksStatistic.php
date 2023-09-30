<?php

namespace App\Command\Task\Statistics;

class FreeTasksStatistic extends AbstractTaskStatistic
{
    protected int $order = 20;
    protected string $title = 'Free tasks';
    protected string $repositoryMethod = 'countFree';
}
