<?php

namespace App\Message;

class EmailNotification
{
    public function __construct(
        private int $notificationId,
    ) {}

    public function getNotificationId(): int
    {
        return $this->notificationId;
    }
}
