<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\Utils\TypeUtils;

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

        if (null !== $type = $data['type'] ?? null) {
            $this->setArgumentType($type);
        }
    }

    public function argument(): string
    {
        return $this->argument;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    private function setArgumentType(string $type): void
    {
        if (!TypeUtils::isCollection($type)) {
            throw new \InvalidArgumentException('Argument type can be specified only for a collection.');
        }

        $this->type = $type;
    }
}
