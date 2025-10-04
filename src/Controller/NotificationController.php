<?php

namespace App\Controller;

use App\Dto\NotificationDto;
use App\Entity\Notification;
use OpenApi\Attributes as OA;
use App\Repository\NotificationRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/notifications', name: 'api_notifications_')]
final class NotificationController extends AbstractController
{
    public function __construct(private readonly NotificationRepository $notificationRepository) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications',
        summary: 'List all notifications',
        tags: ['Notifications']
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of all notifications',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'recipientEmail', type: 'string', example: 'recipient@example.com'),
                    new OA\Property(property: 'subject', type: 'string', example: 'Welcome!'),
                    new OA\Property(property: 'body', type: 'string', example: 'Welcome to our platform'),
                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                ]
            )
        )
    )]
    public function index(): JsonResponse
    {
        $notifications = $this->notificationRepository->findAll();

        $notificationDtos = array_map(
            fn(Notification $notification) => NotificationDto::create($notification),
            $notifications
        );

        return $this->json($notificationDtos);
    }
}
