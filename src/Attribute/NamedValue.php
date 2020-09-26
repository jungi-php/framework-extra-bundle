<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
interface NamedValue extends Attribute
{
    public function name(): ?string;
}
