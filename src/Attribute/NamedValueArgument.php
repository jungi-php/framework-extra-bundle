<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface NamedValueArgument extends Attribute
{
    public function name(): ?string;
}
