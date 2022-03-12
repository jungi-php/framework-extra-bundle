<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class RequestBody
{
    private ?string $type;

    public function __construct(?string $type = null)
    {
        $this->type = $type;
    }

    public function type(): ?string
    {
        return $this->type;
    }
}
