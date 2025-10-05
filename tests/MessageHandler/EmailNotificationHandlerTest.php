<?php

namespace App\Tests\MessageHandler;

use App\Entity\Notification;
use App\Enum\NotificationStatus;
use App\Message\EmailNotification;
use App\MessageHandler\EmailNotificationHandler;
use App\Service\EmailService;
use App\Tests\Factory\NotificationFactory;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

final class EmailNotificationHandlerTest extends KernelTestCase
{
    private Container $container;
    private EmailService $emailService;
    private Notification $notification;
    private EmailNotification $message;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->container = static::getContainer();

        $this->emailService = $this->getMockBuilder(EmailService::class)
            ->setConstructorArgs([$this->container->get(LoggerInterface::class)])
            ->onlyMethods(['sendingMailHasFailed'])
            ->getMock();

        $this->notification = NotificationFactory::createOne();
        $this->message = new EmailNotification($this->notification->getId());
    }

    public function testEmailSendingSuccessful(): void
    {
        // Arrange
        $this->emailService
            ->method('sendingMailHasFailed')
            ->willReturn(false);

        $this->container->set(EmailService::class, $this->emailService);
        $handler = $this->container->get(EmailNotificationHandler::class);

        // Act
        $handler->__invoke($this->message);

        // Assert
        self::assertEquals(NotificationStatus::SENT, $this->notification->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $this->notification->getSentAt());
    }

    public function testEmailSendingFailed(): void
    {
        // Arrange
        $this->emailService
            ->method('sendingMailHasFailed')
            ->willReturn(true);

        $this->container->set(EmailService::class, $this->emailService);
        $handler = $this->container->get(EmailNotificationHandler::class);

        $this->expectException(\RuntimeException::class);

        // Act
        $handler->__invoke($this->message);

        // Assert
        self::assertEquals(NotificationStatus::FAILED, $this->notification->getStatus());
    }
}
