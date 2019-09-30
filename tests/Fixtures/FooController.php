<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\RequestBodyParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestQuery;
use Jungi\FrameworkExtraBundle\Annotation\RequestQueryParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ResponseBody
 */
class FooController extends AbstractController
{
    /**
     * @RequestBodyParam("foo")
     */
    public function withRequestBodyParam(string $foo)
    {
        return 'foo';
    }

    /**
     * @RequestQueryParam("foo")
     */
    public function withRequestQueryParam(string $foo)
    {
        return 'foo';
    }

    /**
     * @RequestBody("foo")
     */
    public function withRequestBody(\stdClass $foo)
    {
        return 'foo';
    }

    /**
     * @RequestQuery("foo")
     */
    public function withRequestQuery(\stdClass $foo)
    {
        return 'foo';
    }

    public function plain()
    {
        return new Response('', 204);
    }

    public function withEntity($entity, $status, $headers)
    {
        return $this->entity($entity, $status, $headers);
    }

    public function withNormalizeEntity($entity, $context, $status, $headers)
    {
        return $this->normalizeEntity($entity, $context, $status, $headers);
    }
}
