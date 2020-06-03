<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface ArgumentAnnotationInterface extends AnnotationInterface
{
    public function getArgumentName(): string;
}
