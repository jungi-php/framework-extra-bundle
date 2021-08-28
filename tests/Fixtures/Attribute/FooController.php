<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use Jungi\FrameworkExtraBundle\Attribute\ResponseBody;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\ForeignAttribute;

class FooController extends AbstractFooController
{
    public function __construct(#[RequestBody] string $param)
    {
    }

    #[ForeignAttribute]
    #[ResponseBody]
    public function withAttributes(#[RequestBody] string $body, #[QueryParam] string $foo, #[QueryParam] string $bar)
    {
    }

    public function withNoAnnotations()
    {
    }

    public function abstractAction(#[RequestParam] string $foo)
    {
    }

    #[ResponseBody]
    protected function protectedAction()
    {
    }

    #[ResponseBody]
    private function privateAction()
    {
    }
}
