<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\NamedValue;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class NamedValueArgument
{
    private string $name;
    private ?string $type;
    private NamedValue $attribute;

    public function __construct(string $name, ?string $type, NamedValue $attribute)
    {
        $this->name = $name;
        $this->type = $type;
        $this->attribute = $attribute;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getAttribute(): NamedValue
    {
        return $this->attribute;
    }
}
