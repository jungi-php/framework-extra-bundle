<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class ExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        switch (true) {
            case $e instanceof NotAcceptableMediaTypeException:
                $event->setThrowable(new NotAcceptableHttpException(sprintf(
                    'Could not respond with any acceptable content types. Only following are supported: %s.',
                    implode(', ', $e->getSupportedMediaTypes())
                ), $e));
                break;
            case $e instanceof UnsupportedMediaTypeException:
                $event->setThrowable(new UnsupportedMediaTypeHttpException(sprintf(
                    'Content type "%s" is not supported.',
                    $e->getMediaType()
                ), $e));
                break;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
