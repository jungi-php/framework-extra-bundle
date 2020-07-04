<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\DependencyInjection\Exportable;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class AbstractAnnotation implements AnnotationInterface, Exportable
{
    public static function __set_state(array $data)
    {
        return new static($data);
    }

    abstract public function __construct(array $data);
}
