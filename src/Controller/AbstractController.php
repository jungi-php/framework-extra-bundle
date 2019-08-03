<?php

namespace Jungi\FrameworkExtraBundle\Controller;

use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class AbstractController extends BaseAbstractController
{
    use ControllerTrait;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), array(
            'jungi.response_factory' => ResponseFactory::class,
        ));
    }
}
