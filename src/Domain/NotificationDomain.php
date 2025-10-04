<?php

namespace App\Domain;

use App\Entity\Notification;
use App\Enum\NotificationStatus;
use App\Repository\NotificationRepository;
use App\Validation\NotificationValidation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class NotificationDomain
{
    public function __construct(
        private readonly NotificationRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Fetch all notifications
     */
    public function getNotifications(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Create new notification
     */
    public function createNotification(NotificationValidation $data): Notification
    {
        $notification = new Notification();

        $notification->setRecipientEmail($data->recipientEmail);
        $notification->setSubject($data->subject);
        $notification->setBody($data->body);
        $notification->setStatus(NotificationStatus::PENDING);
        $notification->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }
}
