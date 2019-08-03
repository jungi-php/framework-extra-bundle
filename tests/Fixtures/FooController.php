<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FooController extends AbstractController
{
    /**
     * @ResponseBody
     */
    public function withResponseBody()
    {
        return array(
            'foo' => 'bar',
        );
    }

    public function plain()
    {
        return new Response('', 204);
    }

    public function withEntity($entity, $status, $headers)
    {
        return $this->entity($entity, $status, $headers);
    }
}
