<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class RequestHeader extends AbstractAnnotation implements NamedValueArgument
{
    private $name;
    private $argument;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? null;
        $this->argument = $data['argument'] ?? $data['value'] ?? null;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function argument(): string
    {
        return $this->argument;
    }
}
