<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class RequestBody extends AbstractAnnotation implements Argument
{
    private $argument;
    private $type;

    public function __construct(array $data)
    {
        $this->argument = $data['argument'] ?? $data['value'] ?? null;
        $this->type = $data['type'] ?? null;
    }

    public function argument(): string
    {
        return $this->argument;
    }

    public function type(): ?string
    {
        return $this->type;
    }
}
