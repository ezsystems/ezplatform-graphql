<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\NotificationService;
use eZ\Publish\API\Repository\Values\Notification\Notification;

class NotificationResolver
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function currentUserNotifications()
    {
        return $this->notificationService->loadNotifications(0, 10);
    }

    public function notificationType(array $notificationData)
    {
        if (isset($notificationData['content_id'])) {
            return 'ContentNotificationData';
        } else {
            return 'NotificationData';
        }
    }
}