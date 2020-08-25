<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class QueryParam extends AbstractAnnotation implements NamedValueArgument
{
    private $name;
    private $argument;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? $data['value'] ?? null;
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
