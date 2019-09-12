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
 */
final class ExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $e = $event->getException();

        switch (true) {
            case $e instanceof NotAcceptableMediaTypeException:
                $event->setException(new NotAcceptableHttpException(sprintf(
                    'Could not response with any acceptable content types. Only following are supported: %s.',
                    implode(', ', $e->getSupportedMediaTypes())
                ), $e));
                break;
            case $e instanceof UnsupportedMediaTypeException:
                $event->setException(new UnsupportedMediaTypeHttpException(sprintf(
                    'Content type "%s" is not supported.',
                    $e->getMediaType()
                ), $e));
                break;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
