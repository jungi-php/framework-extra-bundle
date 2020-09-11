<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;

class BadControllerMethod
{
    /**
     * @ResponseBody
     * @ResponseBody
     */
    public function bad()
    {

    }
}
