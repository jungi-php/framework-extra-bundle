<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @internal
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class RequestParam implements ArgumentAnnotationInterface
{
    /**
     * @Required
     *
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $field;

    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $this->value = $data['value'];
        }
        if (isset($data['field'])) {
            $this->field = $data['field'];
        }
    }

    public function getName(): string
    {
        return $this->value;
    }

    public function getField()
    {
        return $this->field;
    }
}
