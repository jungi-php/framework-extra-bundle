<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\Attribute\RequestHeader as BaseRequestHeader;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class RequestHeader extends BaseRequestHeader implements Argument
{
    use StatefulTrait;

    private $argument;

    public function __construct(array $data)
    {
        parent::__construct($data['name']);

        $this->argument = $data['argument'] ?? $data['value'] ?? null;
    }

    public function argument(): string
    {
        return $this->argument;
    }
}
