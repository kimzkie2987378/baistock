<?php

namespace App\Service;

use App\Entity\Delivery;
use App\Entity\Notification;
use App\Entity\Product;
use App\Repository\DeliveryRepository;
use App\Repository\NotificationRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationRepository $notificationRepository,
        private readonly ProductRepository $productRepository,
        private readonly DeliveryRepository $deliveryRepository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function record(
        string $title,
        string $message,
        string $type = 'general',
        ?string $actionUrl = null,
        ?string $actionText = null
    ): Notification {
        // Prevent re-creating or un-reading an already recorded notification
        $existing = $this->notificationRepository->findOneBy([
            'title' => $title,
        ]);

        if ($existing) {
            // Keep message updated, but NEVER reset isRead back to false if user marked it read!
            $existing->setMessage($message);
            $this->em->flush();
            return $existing;
        }

        $notification = new Notification();
        $notification->setTitle($title)
            ->setMessage($message)
            ->setType($type)
            ->setActionUrl($actionUrl)
            ->setActionText($actionText)
            ->setIsRead(false)
            ->setCreatedAt(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    public function syncSystemAlerts(): void
    {
        // 1. Sync Low / Out of stock
        $lowStockProducts = $this->productRepository->createQueryBuilder('p')
            ->where('p.quantity <= p.minimumStock')
            ->getQuery()
            ->getResult();

        foreach ($lowStockProducts as $p) {
            $isOut = $p->getQuantity() <= 0;
            $type = $isOut ? 'stock_out' : 'stock_low';
            $title = $isOut ? 'Out of Stock: ' . $p->getName() : 'Low Stock Alert: ' . $p->getName();
            $msg = $isOut 
                ? 'Current stock is 0 units (Min: ' . $p->getMinStock() . '). Reorder required.' 
                : 'Only ' . $p->getQuantity() . ' units remaining (Min threshold: ' . $p->getMinStock() . ').';
            $url = $this->urlGenerator->generate('app_inventory_adjust', ['id' => $p->getId()]);
            $action = 'Restock Product';

            $this->record($title, $msg, $type, $url, $action);
        }

        // 2. Sync Pending Deliveries
        $deliveries = $this->deliveryRepository->createQueryBuilder('d')
            ->where('d.isReceived = false OR d.status != :delivered')
            ->setParameter('delivered', 'Delivered')
            ->getQuery()
            ->getResult();

        foreach ($deliveries as $d) {
            $title = 'Shipment #' . $d->getTrackingNumber() . ' (' . $d->getStatus() . ')';
            $msg = 'Supplier: ' . $d->getSupplier() . ' — Total Invoice: ₱' . number_format($d->getTotalCost(), 2);
            if ($d->getStatus() === 'Delivered' && !$d->isReceived()) {
                $msg .= ' (Delivered, awaiting inventory check-in)';
            }
            $url = $this->urlGenerator->generate('app_delivery_index');
            $action = 'View Shipment';

            $this->record($title, $msg, 'delivery', $url, $action);
        }
    }

    public function markAllAsRead(): int
    {
        return $this->notificationRepository->markAllAsRead();
    }

    public function markAsRead(int $id): bool
    {
        $n = $this->notificationRepository->find($id);
        if ($n) {
            $n->setIsRead(true);
            $this->em->flush();
            return true;
        }
        return false;
    }

    public function countUnread(): int
    {
        return $this->notificationRepository->countUnread();
    }
}
