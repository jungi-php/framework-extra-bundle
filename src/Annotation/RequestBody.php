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
    private $value;

    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $this->value = $data['value'];
        }
    }

    public function getName(): string
    {
        return $this->value;
    }
}
