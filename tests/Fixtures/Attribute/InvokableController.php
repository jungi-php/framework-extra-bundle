<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Attribute\ResponseBody;

class InvokableController
{
    #[ResponseBody]
    public function __invoke(#[RequestBody] string $body)
    {
    }
}
