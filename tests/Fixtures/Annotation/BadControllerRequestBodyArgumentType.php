<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;

class BadControllerRequestBodyArgumentType
{
    /**
     * @RequestBody("foo", type="string[]")
     */
    public function bad(string $foo)
    {

    }
}
