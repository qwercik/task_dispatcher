<?php

namespace App\Service;

use App\Entity\Task;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskAccessService
{
    public function __construct(
        protected Security $security
    ) {}

    public function canAccessTaskForRead(Task $task): void
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $this->validateTaskAccess($task);
        } elseif ($task === null) {
            throw new NotFoundHttpException("Task does not exist");
        }
    }

    public function canAccessTaskForWrite(Task $task): void
    {
        if ($task !== null) {
            $this->validateTaskAccess($task);
        } elseif ($this->security->isGranted('ROLE_ADMIN')) {
            throw new NotFoundHttpException("Task does not exist");
        } else {
            throw new AccessDeniedHttpException("You haven't reserved this task");
        }
    }

    private function validateTaskAccess(?Task $task): void
    {
        if ($task === null || $task->getUser() !== $this->security->getUser()) {
            throw new AccessDeniedHttpException("You haven't reserved this task");
        } elseif ($task->getFinishedAt() !== null) {
            throw new AccessDeniedHttpException("Task has been already finished");
        }
    }
}