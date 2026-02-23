<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('/mark-as-read/{id}', name: 'app_notification_read')]
    public function markAsRead(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        if ($notification->getRecipient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $notification->setIsRead(true);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/clear-all', name: 'app_notification_clear_all')]
    public function clearAll(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $notifications = $user->getNotifications();

        foreach ($notifications as $notification) {
            if (!$notification->isRead()) {
                $notification->setIsRead(true);
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }
}
