<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class QueryParam implements NamedValue
{
    private $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
