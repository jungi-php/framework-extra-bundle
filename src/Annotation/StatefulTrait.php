<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
trait StatefulTrait
{
    public static function __set_state(array $data)
    {
        return new static($data);
    }

    abstract public function __construct(array $data);
}
