<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class RequestHeader implements NamedValue
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
