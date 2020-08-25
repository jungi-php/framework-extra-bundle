<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface NamedValueArgument extends Argument
{
    public function name(): string;
}
