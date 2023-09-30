<?php

namespace App\Command\Task\Statistics;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.task.statistic')]
interface TaskStatisticInterface
{
    public function getOrder(): int;
    public function getTitle(): string;
    public function getFormattedValue(): string;
}
