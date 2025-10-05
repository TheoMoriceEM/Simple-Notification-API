<?php

namespace App\Domain;

use App\Entity\Notification;
use App\Enum\NotificationStatus;
use App\Message\EmailNotification;
use App\Repository\NotificationRepository;
use App\Validation\NotificationValidation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationDomain
{
    public function __construct(
        private readonly NotificationRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
    ) {}

    /**
     * Fetch all notifications
     */
    public function getNotifications(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Fetch a notification
     */
    public function getNotification(int $id): ?Notification
    {
        $notification = $this->repository->find($id);

        if (!$notification) {
            throw new NotFoundHttpException(sprintf('Notification with id %s not found.', $id));
        }

        return $notification;
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

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    /**
     * Send a notification
     */
    public function sendNotification(int $id): Notification
    {
        $notification = $this->getNotification($id);

        if ($notification->getStatus() === NotificationStatus::SENT) {
            throw new BadRequestHttpException(sprintf('Notification with id %s has already been sent.', $id));
        }

        $this->bus->dispatch(new EmailNotification($id));

        return $notification;
    }
}
