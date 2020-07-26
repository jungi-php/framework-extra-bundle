<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface ArgumentInterface extends AnnotationInterface
{
    public function getArgumentName(): string;
}
