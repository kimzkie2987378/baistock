<?php

namespace App\Repository;

use App\Entity\SaleItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleItem>
 */
class SaleItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleItem::class);
    }

    public function getTopSellingProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('si')
            ->leftJoin('si.product', 'p')
            ->select('COALESCE(p.productName, \'Item\') as name, SUM(si.quantity) as totalQty, SUM(si.subtotal) as totalRevenue')
            ->groupBy('p.productName')
            ->orderBy('totalQty', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
