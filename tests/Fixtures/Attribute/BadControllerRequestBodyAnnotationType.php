<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;

class BadControllerRequestBodyAnnotationType
{
    public function bad(#[RequestBody('string')] array $foo)
    {
    }
}
