<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function getOneFree(): ?Task
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT t
                FROM App\Entity\Task t
                WHERE t.reserved_at IS NULL
            ')
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    public function countAll(): int
    {
        return $this->count([]);
    }

    public function countFree(): int
    {
        return $this->count([
            'reserved_at' => null,
            'finished_at' => null,
        ]);
    }

    public function countReserved(): int
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(t.id)
                FROM App\Entity\Task t
                WHERE t.reserved_at IS NOT NULL
                AND t.finished_at IS NULL
            ')
            ->getSingleScalarResult()
        ;
    }

    public function countCompleted(): int
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(t.id)
                FROM App\Entity\Task t
                WHERE t.finished_at IS NOT NULL
            ')
            ->getSingleScalarResult()
        ;
    }

    public function countProgress(): ?float
    {
        $all = $this->countAll();
        if ($all === 0) {
            return null;
        }

        $completed = $this->countCompleted();
        return 100 * $completed / $all;
    }

    public function findAllCompleted(): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT t
                FROM App\Entity\Task t
                WHERE t.finished_at IS NOT NULL
            ')
            ->getResult()
        ;
    }

    public function findReservedByUser(User $user)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT t
                FROM App\Entity\Task t
                WHERE t.reserved_at IS NOT NULL
                    AND t.finished_at IS NULL
                    AND t.user = :user
            ')
            ->setParameter('user', $user)
            ->getResult()
        ;
    }
}
