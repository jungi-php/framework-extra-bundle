<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;

class BadControllerRequestBodyAnnotationType
{
    /**
     * @RequestBody("foo", type="string")
     */
    public function bad(array $foo)
    {

    }
}
