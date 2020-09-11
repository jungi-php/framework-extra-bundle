<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;

class InvokableController
{
    /**
     * @RequestBody("body")
     * @ResponseBody
     */
    public function __invoke(string $body)
    {
    }
}
