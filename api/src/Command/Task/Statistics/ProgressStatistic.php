<?php

namespace App\Command\Task\Statistics;

class ProgressStatistic extends AbstractTaskStatistic
{
    protected int $order = 50;
    protected string $title = 'Progress (%)';
    protected string $repositoryMethod = 'countProgress';
}
