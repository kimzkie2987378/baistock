<?php

namespace App\Repository;

use App\Entity\StockMovement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockMovement>
 */
class StockMovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockMovement::class);
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('sm')
            ->leftJoin('sm.product', 'p')
            ->addSelect('p')
            ->leftJoin('sm.user', 'u')
            ->addSelect('u')
            ->orderBy('sm.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
