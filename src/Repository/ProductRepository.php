<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findLowStock(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->where('p.quantity <= p.minimumStock')
            ->orderBy('p.quantity', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countLowStock(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.quantity <= p.minimumStock')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalStockUnits(): int
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.quantity)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    public function searchAndFilter(?string $query = null, ?string $category = null, ?string $stockStatus = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        if ($query) {
            $qb->andWhere('LOWER(p.productName) LIKE :q OR LOWER(c.categoryName) LIKE :q')
                ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($category && $category !== 'all') {
            $qb->andWhere('c.categoryName = :category')
                ->setParameter('category', $category);
        }

        if ($stockStatus) {
            if ($stockStatus === 'low') {
                $qb->andWhere('p.quantity > 0 AND p.quantity <= p.minimumStock');
            } elseif ($stockStatus === 'out') {
                $qb->andWhere('p.quantity <= 0');
            } elseif ($stockStatus === 'in') {
                $qb->andWhere('p.quantity > p.minimumStock');
            }
        }

        return $qb->orderBy('p.productName', 'ASC')->getQuery()->getResult();
    }

    public function getCategories(): array
    {
        return ['Snacks', 'Fruits', 'Canned Goods', 'Vegetables', 'Groceries'];
    }
}
