<?php

namespace App\Twig;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationExtension extends AbstractExtension
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationService $notificationService,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_notifications', [$this, 'getNotifications']),
        ];
    }

    public function getNotifications(): array
    {
        // Automatically sync system alerts so new shipments or stock changes are captured
        $this->notificationService->syncSystemAlerts();

        $unreadCount = $this->notificationRepository->countUnread();
        $recentNotifications = $this->notificationRepository->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(12)
            ->getQuery()
            ->getResult();

        $items = [];
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $deliveryCount = 0;

        foreach ($recentNotifications as $n) {
            $isUnread = !$n->isRead();
            $type = $n->getType();

            if ($type === 'stock_out') {
                if ($isUnread) $outOfStockCount++;
                $badge = 'Out of Stock';
                $badgeClass = 'bg-rose-100 text-rose-700 border border-rose-200';
                $icon = 'alert-octagon';
                $iconBg = 'bg-rose-50 text-rose-600 border border-rose-100';
            } elseif ($type === 'stock_low') {
                if ($isUnread) $lowStockCount++;
                $badge = 'Low Stock';
                $badgeClass = 'bg-amber-100 text-amber-700 border border-amber-200';
                $icon = 'alert-triangle';
                $iconBg = 'bg-amber-50 text-amber-600 border border-amber-100';
            } elseif ($type === 'delivery') {
                if ($isUnread) $deliveryCount++;
                $badge = 'Delivery';
                $badgeClass = 'bg-blue-100 text-blue-700 border border-blue-200';
                $icon = 'truck';
                $iconBg = 'bg-blue-50 text-blue-600 border border-blue-100';
            } else {
                $badge = 'Alert';
                $badgeClass = 'bg-purple-100 text-purple-700 border border-purple-200';
                $icon = 'bell';
                $iconBg = 'bg-purple-50 text-purple-600 border border-purple-100';
            }

            $items[] = [
                'id' => $n->getId(),
                'type' => $type === 'delivery' ? 'delivery' : 'stock',
                'raw_type' => $type,
                'title' => $n->getTitle(),
                'message' => $n->getMessage(),
                'badge' => $badge,
                'badge_class' => $badgeClass,
                'icon' => $icon,
                'icon_bg' => $iconBg,
                'url' => $n->getActionUrl() ?: $this->urlGenerator->generate('app_notifications_index'),
                'action_text' => $n->getActionText() ?: 'View Details',
                'is_read' => $n->isRead(),
                'timestamp' => $n->getCreatedAt() ? $n->getCreatedAt()->format('M d, h:i A') : 'Recent',
            ];
        }

        return [
            'unread_count' => $unreadCount,
            'total_count' => $unreadCount,
            'recorded_count' => count($items),
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'pending_delivery_count' => $deliveryCount,
            'items' => $items,
        ];
    }
}
