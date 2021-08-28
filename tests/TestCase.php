<?php

namespace Jungi\FrameworkExtraBundle\Tests;

use Jungi\FrameworkExtraBundle\Annotation\Annotation;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param Annotation[] $annotations
     */
    protected function createAnnotationContainer(array $annotations): ContainerInterface
    {
        $map = array();
        foreach ($annotations as $annotation) {
            $map[get_parent_class($annotation)] = $annotation;
        }

        return new SimpleContainer($map);
    }
}
