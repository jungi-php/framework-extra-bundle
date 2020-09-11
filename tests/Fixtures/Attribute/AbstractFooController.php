<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractFooController extends AbstractController
{
    #[QueryParam('foo')]
    abstract public function abstractAction(string $foo);
}
