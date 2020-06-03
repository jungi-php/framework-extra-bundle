<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestQueryParam implements RequestFieldAnnotationInterface
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
        if (isset($data['value'])) {
            $this->argumentName = $data['value'];
            $this->fieldName = $data['value'];
        }
        if (isset($data['argument'])) {
            $this->argumentName = $data['argument'];
        }
        if (isset($data['field'])) {
            $this->fieldName = $data['field'];
        }
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
