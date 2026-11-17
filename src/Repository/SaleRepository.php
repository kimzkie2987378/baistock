<?php

namespace App\Repository;

use App\Entity\Sale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sale>
 */
class SaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    public function findRecentSales(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.items', 'i')
            ->addSelect('i')
            ->leftJoin('i.product', 'p')
            ->addSelect('p')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTotalSalesAmount(): float
    {
        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.totalAmount)')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function getTodaySalesAmount(): float
    {
        $start = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
        $end = (new \DateTimeImmutable('today'))->setTime(23, 59, 59);

        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.totalAmount)')
            ->where('s.createdAt >= :start AND s.createdAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function getThisWeekSalesAmount(): float
    {
        $start = (new \DateTimeImmutable('monday this week'))->setTime(0, 0, 0);
        $end = (new \DateTimeImmutable('sunday this week'))->setTime(23, 59, 59);

        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.totalAmount)')
            ->where('s.createdAt >= :start AND s.createdAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    public function getSalesByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.items', 'i')
            ->addSelect('i')
            ->where('s.createdAt >= :start AND s.createdAt <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Return daily sales summary for the last 7 days ending today
     */
    public function getDailySalesHistory(int $days = 7): array
    {
        $endDate = new \DateTimeImmutable('today 23:59:59');
        $startDate = $endDate->modify('-' . ($days - 1) . ' days')->setTime(0, 0, 0);

        $sales = $this->createQueryBuilder('s')
            ->where('s.createdAt >= :start AND s.createdAt <= :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();

        $dailyMap = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dt = (new \DateTimeImmutable())->modify("-$i days");
            $key = $dt->format('Y-m-d');
            $label = $dt->format('M d');
            $dailyMap[$key] = [
                'label' => $label,
                'total' => 0.0,
                'count' => 0,
            ];
        }

        foreach ($sales as $sale) {
            $key = $sale->getCreatedAt()->format('Y-m-d');
            if (isset($dailyMap[$key])) {
                $dailyMap[$key]['total'] += (float)$sale->getTotalAmount();
                $dailyMap[$key]['count'] += 1;
            }
        }

        return array_values($dailyMap);
    }

    /**
     * Return weekly sales summary for the last 4 weeks ending this week
     */
    public function getWeeklySalesHistory(int $weeks = 4): array
    {
        $weeklyMap = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = (new \DateTimeImmutable("-$i weeks"))->modify('monday this week')->setTime(0, 0, 0);
            $end = (new \DateTimeImmutable("-$i weeks"))->modify('sunday this week')->setTime(23, 59, 59);
            $label = $i === 0 ? 'This Week' : 'Week ' . ($weeks - $i);
            
            $result = $this->createQueryBuilder('s')
                ->select('SUM(s.totalAmount)')
                ->where('s.createdAt >= :start AND s.createdAt <= :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->getQuery()
                ->getSingleScalarResult();

            $weeklyMap[] = [
                'label' => $label,
                'total' => (float)($result ?? 0),
            ];
        }

        return $weeklyMap;
    }

    /**
     * Return monthly sales summary for the last 6 months ending current month
     */
    public function getMonthlySalesHistory(int $months = 6): array
    {
        $monthlyMap = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $dt = (new \DateTimeImmutable("first day of -$i month"))->setTime(0, 0, 0);
            $start = $dt;
            $end = (new \DateTimeImmutable("last day of -$i month"))->setTime(23, 59, 59);
            $label = $dt->format('M Y');

            $result = $this->createQueryBuilder('s')
                ->select('SUM(s.totalAmount)')
                ->where('s.createdAt >= :start AND s.createdAt <= :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->getQuery()
                ->getSingleScalarResult();

            $monthlyMap[] = [
                'label' => $label,
                'total' => (float)($result ?? 0),
            ];
        }

        return $monthlyMap;
    }
}
