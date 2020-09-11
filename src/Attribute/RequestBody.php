<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

use Attribute as PhpAttribute;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @final
 */
#[PhpAttribute(PhpAttribute::TARGET_PARAMETER)]
class RequestBody implements Attribute
{
    private $type;

    public static function __set_state(array $data)
    {
        return new self($data['type'] ?? null);
    }

    public function __construct(?string $type = null)
    {
        $this->type = $type;
    }

    public function type(): ?string
    {
        return $this->type;
    }
}
