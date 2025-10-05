<?php

namespace App\MessageHandler;

use App\Enum\NotificationStatus;
use App\Domain\NotificationDomain;
use App\Message\EmailNotification;
use App\Service\EmailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailNotificationHandler
{
    public function __construct(
        private readonly NotificationDomain $notificationDomain,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly EmailService $emailService,
    ) {}

    public function __invoke(EmailNotification $message)
    {
        $this->logger->info('Processing sending notification via email...', [
            'notification_id' => $message->getNotificationId(),
        ]);

        $notification = $this->notificationDomain->getNotification($message->getNotificationId());

        try {
            $this->emailService->send($notification);

            $this->logger->info('Notification sent successfully!', [
                'notification_id' => $message->getNotificationId(),
            ]);

            $notification->setStatus(NotificationStatus::SENT);
            $notification->setSentAt(new DateTimeImmutable());
        } catch (Exception $e) {
            $this->logger->error('Sending notification failed.', [
                'notification_id' => $message->getNotificationId(),
                'message' => $e->getMessage(),
            ]);

            $notification->setStatus(NotificationStatus::FAILED);

            throw $e;
        } finally {
            $this->em->persist($notification);
            $this->em->flush();
        }
    }
}
