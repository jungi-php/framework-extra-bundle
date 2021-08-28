<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\ForeignAttribute;

class FooController extends AbstractFooController
{
    /**
     * @RequestBody("body")
     */
    public function __construct()
    {
    }

    /**
     * @ForeignAttribute
     * @RequestBody("body")
     * @QueryParam("foo")
     * @QueryParam("bar")
     * @ResponseBody
     */
    public function withAttributes(string $body, string $foo, string $bar)
    {
    }

    public function withNoAnnotations()
    {
    }

    /** @RequestParam("foo") */
    public function abstractAction(string $foo)
    {
    }

    /** @ResponseBody */
    protected function protectedAction()
    {
    }

    /** @ResponseBody */
    private function privateAction()
    {
    }
}
