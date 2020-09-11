<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;

class BadControllerArgument
{
    /**
     * @QueryParam("foo")
     * @QueryParam("foo")
     */
    public function bad($foo)
    {
    }
}
