<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUsersWithCompletedTasksSummary(): array
    {
        return $this->getEntityManager()
            ->createQuery('SELECT
                    u AS user,
                    (SELECT COUNT(t1) FROM App\Entity\Task t1 WHERE t1.user = u AND t1.reserved_at IS NOT NULL AND t1.finished_at IS NULL) AS reserved_tasks_count,
                    (SELECT COUNT(t2) FROM App\Entity\Task t2 WHERE t2.user = u AND t2.finished_at IS NOT NULL) AS completed_tasks_count
                FROM App\Entity\User u
            ')
            ->getResult();
    }
}
