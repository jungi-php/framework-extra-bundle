<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;

class BadControllerRequestBodyArgumentType
{
    public function bad(#[RequestBody('string[]')] string $foo)
    {
    }
}
