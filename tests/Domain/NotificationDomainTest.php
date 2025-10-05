<?php

namespace App\Tests\Domain;

use App\Domain\NotificationDomain;
use App\Entity\Notification;
use App\Enum\NotificationStatus;
use App\Tests\Factory\NotificationFactory;
use App\Validation\NotificationValidation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NotificationDomainTest extends KernelTestCase
{
    private NotificationDomain $domain;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->domain = $container->get(NotificationDomain::class);
    }

    public function testGetNotifications()
    {
        // Arrange
        NotificationFactory::createMany(2);

        // Act
        $notifications = $this->domain->getNotifications();

        // Assert
        self::assertCount(2, $notifications);
    }

    public function testCreateNotification(): void
    {
        // Arrange
        $data = [
            'recipientEmail' => 'recipient@example.com',
            'subject' => 'Welcome!',
            'body' => 'Welcome to our platform.',
        ];

        $validation = new NotificationValidation();
        $validation->recipientEmail = $data['recipientEmail'];
        $validation->subject = $data['subject'];
        $validation->body = $data['body'];

        // Act
        $notification = $this->domain->createNotification($validation);

        // Assert
        self::assertInstanceOf(Notification::class, $notification);
        self::assertEquals($data['recipientEmail'], $notification->getRecipientEmail());
        self::assertEquals($data['subject'], $notification->getSubject());
        self::assertEquals($data['body'], $notification->getBody());
        self::assertEquals(NotificationStatus::PENDING, $notification->getStatus());
    }

    public function testSendAlreadySentNotification(): void
    {
        // Arrange
        $notification = NotificationFactory::createOne(['status' => NotificationStatus::SENT]);
        $this->expectException(BadRequestHttpException::class);

        // Act
        $this->domain->sendNotification($notification->getId());
    }
}
