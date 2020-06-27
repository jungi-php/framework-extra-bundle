<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\DependencyInjection\StatefulObject;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class AbstractAnnotation implements AnnotationInterface, StatefulObject
{
    public static function fromState(array $data)
    {
        return new static($data);
    }

    abstract public function __construct(array $data);
}
