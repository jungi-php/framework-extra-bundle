<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface RequestFieldAnnotationInterface extends ArgumentAnnotationInterface
{
    public function getFieldName(): string;
}
