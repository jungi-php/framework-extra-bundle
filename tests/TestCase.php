<?php

namespace Jungi\FrameworkExtraBundle\Tests;

use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    protected function createAttributeContainer(array $attributes): ContainerInterface
    {
        $map = array();
        foreach ($attributes as $attribute) {
            $map[get_class($attribute)] = $attribute;
        }

        return new SimpleContainer($map);
    }
}
