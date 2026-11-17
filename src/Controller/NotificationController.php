<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('', name: 'app_notifications_index', methods: ['GET'])]
    public function index(
        Request $request,
        NotificationRepository $notificationRepository,
        NotificationService $notificationService
    ): Response {
        // Sync any active operational alerts so history is always current
        $notificationService->syncSystemAlerts();

        $filter = $request->query->get('filter', 'all');

        $type = null;
        $isRead = null;

        if ($filter === 'unread') {
            $isRead = false;
        } elseif ($filter === 'read') {
            $isRead = true;
        } elseif ($filter === 'stock') {
            $type = 'stock';
        } elseif ($filter === 'delivery') {
            $type = 'delivery';
        }

        $qb = $notificationRepository->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC');

        if ($type === 'stock') {
            $qb->andWhere('n.type IN (:stockTypes)')
                ->setParameter('stockTypes', ['stock_low', 'stock_out']);
        } elseif ($type === 'delivery') {
            $qb->andWhere('n.type = :delivType')
                ->setParameter('delivType', 'delivery');
        }

        if ($isRead !== null) {
            $qb->andWhere('n.isRead = :isRead')
                ->setParameter('isRead', $isRead);
        }

        $notifications = $qb->getQuery()->getResult();

        $unreadCount = $notificationRepository->countUnread();
        $totalCount = (int) $notificationRepository->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $stockAlertsCount = (int) $notificationRepository->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.type IN (:stockTypes) AND n.isRead = false')
            ->setParameter('stockTypes', ['stock_low', 'stock_out'])
            ->getQuery()
            ->getSingleScalarResult();

        $deliveryAlertsCount = (int) $notificationRepository->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.type = :delivType AND n.isRead = false')
            ->setParameter('delivType', 'delivery')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
            'current_filter' => $filter,
            'unread_count' => $unreadCount,
            'total_count' => $totalCount,
            'stock_alerts_count' => $stockAlertsCount,
            'delivery_alerts_count' => $deliveryAlertsCount,
        ]);
    }

    #[Route('/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllRead(
        Request $request,
        NotificationService $notificationService
    ): Response {
        $token = $request->request->get('_token');
        if (!$token || $this->isCsrfTokenValid('mark_all_notifications_read', $token)) {
            $count = $notificationService->markAllAsRead();
            $this->addFlash('success', "All notifications marked as read ($count updated).");
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_notifications_index'));
    }

    #[Route('/{id}/mark-read', name: 'app_notifications_mark_read', methods: ['POST'])]
    public function markRead(
        Notification $notification,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $token = $request->request->get('_token');
        if (!$token || $this->isCsrfTokenValid('mark_read_' . $notification->getId(), $token)) {
            $notification->setIsRead(true);
            $em->flush();
            $this->addFlash('success', 'Notification marked as read.');
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_notifications_index'));
    }

    #[Route('/{id}/delete', name: 'app_notifications_delete', methods: ['POST'])]
    public function delete(
        Notification $notification,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_notif_' . $notification->getId(), $token)) {
            $em->remove($notification);
            $em->flush();
            $this->addFlash('success', 'Notification removed.');
        }

        return $this->redirectToRoute('app_notifications_index');
    }
}
