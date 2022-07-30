<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout', 64],
        ];
    }

    /**
     * render json error.
     */
    public function onLogout(LogoutEvent $event): void
    {
        $event->setResponse(new JsonResponse([
            'message' => 'Goodbye.',
        ]));
    }
}
