<?php

namespace App\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        // Get the exception object from the received event
        $exception = $event->getThrowable();
        // Handle the exception or modify the response based on its type
        if ($exception instanceof HttpException) {
            $event->setResponse(new JsonResponse(json_decode($exception->getMessage(), true), $exception->getStatusCode()));
        }
    }
}