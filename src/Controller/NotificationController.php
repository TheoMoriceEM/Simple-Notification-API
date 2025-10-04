<?php

namespace App\Controller;

use App\Dto\NotificationDto;
use App\Entity\Notification;
use OpenApi\Attributes as OA;
use App\Domain\NotificationDomain;
use App\Validation\NotificationValidation;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/notifications', name: 'api_notifications_')]
#[OA\Tag(name: 'Notifications')]
final class NotificationController extends AbstractController
{
    public function __construct(private readonly NotificationDomain $domain) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications',
        summary: 'List all notifications',
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
                    new OA\Property(property: 'body', type: 'string', example: 'Welcome to our platform.'),
                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                    new OA\Property(property: 'createdAt', type: 'string', example: '2025-01-01 00:00:00'),
                    new OA\Property(property: 'sentAt', type: 'string', example: '2025-01-01 00:01:00'),
                ]
            )
        )
    )]
    public function index(): JsonResponse
    {
        $notifications = $this->domain->getNotifications();

        $dtos = array_map(
            fn(Notification $notification) => NotificationDto::create($notification),
            $notifications
        );

        return $this->json($dtos);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notifications',
        summary: 'Create a new notification',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['recipientEmail', 'subject', 'body'],
                properties: [
                    new OA\Property(
                        property: 'recipientEmail',
                        type: 'string',
                        format: 'email',
                        example: 'recipient@example.com'
                    ),
                    new OA\Property(
                        property: 'subject',
                        type: 'string',
                        example: 'Welcome!'
                    ),
                    new OA\Property(
                        property: 'body',
                        type: 'string',
                        example: 'Welcome to our platform.'
                    ),
                ]
            )
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Notification created successfully'
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input data'
    )]
    public function create(#[MapRequestPayload(validationGroups: ['create'])] NotificationValidation $data): JsonResponse
    {
        $notification = $this->domain->createNotification($data);
        $dto = NotificationDto::create($notification);

        return $this->json($dto, Response::HTTP_CREATED);
    }

    #[Route('/{id}/send', name: 'send', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notifications/{id}/send',
        summary: 'Send a notification',
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Notification ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(response: 200, description: 'Notification queued for sending')]
    #[OA\Response(response: 404, description: 'Notification not found')]
    #[OA\Response(response: 400, description: 'Notification already sent')]
    public function send(int $id): JsonResponse
    {
        $notification = $this->domain->sendNotification($id);

        return $this->json(NotificationDto::create($notification));
    }
}
