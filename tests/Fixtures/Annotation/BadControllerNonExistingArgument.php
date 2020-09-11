<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;

class BadControllerNonExistingArgument
{
    /**
     * @QueryParam("bar")
     */
    public function bad($foo)
    {

    }
}
