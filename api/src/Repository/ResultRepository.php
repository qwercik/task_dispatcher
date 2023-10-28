<?php

namespace App\Repository;

use App\Entity\Result;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Result>
 *
 * @method Result|null find($id, $lockMode = null, $lockVersion = null)
 * @method Result|null findOneBy(array $criteria, array $orderBy = null)
 * @method Result[]    findAll()
 * @method Result[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Result::class);
    }

    public function findAllIdsByTask(Task $task): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT r.id FROM App\Entity\Result r
                WHERE r.task = :task
            ')
            ->setParameter('task', $task)
            ->getResult()
        ;
    }

    public function deleteAllByTask(Task $task): void
    {
        $this->getEntityManager()
            ->createQuery('
                DELETE FROM App\Entity\Result r
                WHERE r.task = :task
            ')
            ->setParameter('task', $task)
            ->execute()
        ;
    }
}
