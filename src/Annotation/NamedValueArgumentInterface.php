<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface NamedValueArgumentInterface extends ArgumentInterface
{
    public function getName(): string;
}