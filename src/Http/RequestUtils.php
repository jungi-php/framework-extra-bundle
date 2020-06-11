<?php

namespace Jungi\FrameworkExtraBundle\Http;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestUtils
{
    public static function getControllerAnnotationRegistry(Request $request): ?ClassMethodAnnotationRegistry
    {
        return $request->attributes->get('_jungi_controller_annotation_registry');
    }

    public static function setControllerAnnotationRegistry(Request $request, ClassMethodAnnotationRegistry $annotationRegistry): void
    {
        $request->attributes->set('_jungi_controller_annotation_registry', $annotationRegistry);
    }
}
