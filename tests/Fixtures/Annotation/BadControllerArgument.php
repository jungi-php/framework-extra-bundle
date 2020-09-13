<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;

class BadControllerArgument
{
    /**
     * @QueryParam("foo")
     * @RequestParam("foo")
     */
    public function bad($foo)
    {
    }
}
