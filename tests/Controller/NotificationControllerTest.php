<?php

namespace App\Tests\Controller;

use App\Tests\Factory\NotificationFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class NotificationControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        // Arrange
        NotificationFactory::createMany(2);

        // Act
        $this->client->request('GET', '/api/notifications');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertCount(2, $response);
    }

    public function testCreate(): void
    {
        // Act
        $this->client->request('POST', '/api/notifications', [
            'recipientEmail' => 'recipient@example.com',
            'subject' => 'Welcome!',
            'body' => 'Welcome to our platform.',
        ]);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertEquals('recipient@example.com', $response['recipientEmail']);
    }

    public function testCreateInvalidData(): void
    {
        // Act
        $this->client->request('POST', '/api/notifications', [
            'recipientEmail' => 'recipient',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSend(): void
    {
        // Arrange
        $notification = NotificationFactory::createOne();

        // Act
        $this->client->request('POST', '/api/notifications/' . $notification->getId() . '/send');

        // Assert
        self::assertResponseIsSuccessful();
    }
}
