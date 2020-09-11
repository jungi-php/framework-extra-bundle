<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractFooController extends AbstractController
{
    /** @QueryParam("foo") */
    abstract public function abstractAction(string $foo);
}
