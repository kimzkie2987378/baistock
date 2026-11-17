<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function countUnread(): int
    {
        return (int) $this->getEntityManager()
            ->getConnection()
            ->fetchOne('SELECT COUNT(id) FROM notifications WHERE is_read = 0');
    }

    public function markAllAsRead(): int
    {
        return (int) $this->getEntityManager()
            ->getConnection()
            ->executeStatement('UPDATE notifications SET is_read = 1 WHERE is_read = 0');
    }

    /**
     * @return Notification[]
     */
    public function findLatest(int $limit = 50, ?string $type = null, ?bool $isRead = null): array
    {
        $qb = $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($type && $type !== 'all') {
            $qb->andWhere('n.type = :type')
                ->setParameter('type', $type);
        }

        if ($isRead !== null) {
            $qb->andWhere('n.isRead = :isRead')
                ->setParameter('isRead', $isRead);
        }

        return $qb->getQuery()->getResult();
    }
}
