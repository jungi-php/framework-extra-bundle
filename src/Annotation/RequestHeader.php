<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestHeader extends AbstractAnnotation implements NamedValueArgumentInterface
{
    /**
     * @Required
     *
     * @var string
     */
    private $name;

    /**
     * @Required
     *
     * @var string
     */
    private $argumentName;

    public function __construct(array $data)
    {
        $this->argumentName = $data['argumentName'] ?? $data['argument'] ?? $data['value'] ?? null;
        $this->name = $data['name'] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }
}
