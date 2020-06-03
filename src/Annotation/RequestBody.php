<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestBody implements ArgumentAnnotationInterface
{
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
        }
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }
}
