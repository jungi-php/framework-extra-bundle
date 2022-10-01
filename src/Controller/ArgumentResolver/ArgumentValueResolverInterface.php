<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface as BaseArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

if (class_exists(ValueResolverInterface::class)) {
    /** @internal */
    interface ArgumentValueResolverInterface extends BaseArgumentValueResolverInterface, ValueResolverInterface
    {
    }
} else {
    /** @internal */
    interface ArgumentValueResolverInterface extends BaseArgumentValueResolverInterface
    {
    }
}
