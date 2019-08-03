<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestBody
{
    private $name;

    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $this->name = $data['value'];
        }
    }

    public function getName()
    {
        return $this->name;
    }
}
