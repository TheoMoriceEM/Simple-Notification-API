<?php

namespace App\Dto;

use App\Entity\Notification;

class NotificationDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $recipientEmail,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly ?string $sentAt,
    ) {}

    public static function create(Notification $notification): self
    {
        return new self(
            id: $notification->getId(),
            recipientEmail: $notification->getRecipientEmail(),
            subject: $notification->getSubject(),
            body: $notification->getBody(),
            status: $notification->getStatus()->value,
            createdAt: $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            sentAt: $notification->getSentAt()?->format('Y-m-d H:i:s'),
        );
    }
}
