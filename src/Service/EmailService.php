<?php

namespace App\Service;

use App\Entity\Notification;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function send(Notification $notification): void
    {
        $this->logger->debug('Sending email...', [
            'recipient_email' => $notification->getRecipientEmail(),
        ]);

        sleep(3); // Simulate short delay for sending email...

        // Simulate a random failure once in a while
        if (rand(1, 100) <= 10) {
            $this->logger->error('Email sending failed.', [
                'recipient_email' => $notification->getRecipientEmail(),
            ]);
            throw new \RuntimeException('Email sending failed for whatever reason.');
        }

        $this->logger->debug('Email sent successfully!', [
            'recipient_email' => $notification->getRecipientEmail(),
        ]);
    }
}
