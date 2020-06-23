<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Jungi\FrameworkExtraBundle\Annotation\AnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\ArgumentAnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class AnnotationsListener implements EventSubscriberInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if (!is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!is_array($controller)) {
            return;
        }

        $classRefl = new \ReflectionClass($controller[0]);
        $methodRefl = $classRefl->getMethod($controller[1]);

        $classAnnotations = [];
        foreach ($this->reader->getClassAnnotations($classRefl) as $annotation) {
            if ($annotation instanceof AnnotationInterface) {
                $classAnnotations[] = $annotation;
            }
        }

        $methodAnnotations = [];
        $argumentAnnotations = [];
        $existingParameters = [];

        foreach ($methodRefl->getParameters() as $parameter) {
            $existingParameters[] = $parameter->getName();
        }

        foreach ($this->reader->getMethodAnnotations($methodRefl) as $annotation) {
            if ($annotation instanceof ArgumentAnnotationInterface) {
                if (!in_array($annotation->getArgumentName(), $existingParameters, true)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Expected to have the argument "%s" in "%s::%s", but it\'s not present.',
                        $annotation->getArgumentName(),
                        $classRefl->getName(),
                        $methodRefl->getName()
                    ));
                }

                $argumentAnnotations[] = $annotation;
            } elseif ($annotation instanceof AnnotationInterface) {
                $methodAnnotations[] = $annotation;
            }
        }

        RequestUtils::setControllerAnnotationRegistry(
            $request,
            new ClassMethodAnnotationRegistry($classAnnotations, $methodAnnotations, $argumentAnnotations)
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
