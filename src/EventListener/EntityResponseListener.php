<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Http\EntityResponse;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class EntityResponseListener implements EventSubscriberInterface
{
    private const DEFAULT_CONTENT_TYPE = 'application/json';

    private MessageBodyMapperManager $messageBodyMapperManager;
    private string $defaultContentType;

    public function __construct(MessageBodyMapperManager $messageBodyMapperManager, string $defaultContentType = self::DEFAULT_CONTENT_TYPE)
    {
        $this->messageBodyMapperManager = $messageBodyMapperManager;
        $this->defaultContentType = $defaultContentType;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        if ($response instanceof EntityResponse) {
            $response->negotiateContent($event->getRequest(), $this->messageBodyMapperManager, $this->defaultContentType);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}