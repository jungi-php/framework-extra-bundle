<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestCookie extends AbstractAnnotation implements RequestFieldAnnotationInterface
{
    /**
     * @Required
     *
     * @var string
     */
    private $fieldName;

    /**
     * @Required
     *
     * @var string
     */
    private $argumentName;

    public function __construct(array $data)
    {
        $this->argumentName = $data['argumentName'] ?? $data['argument'] ?? $data['value'] ?? null;
        $this->fieldName = $data['fieldName'] ?? $data['field'] ?? $data['value'] ?? null;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }
}
