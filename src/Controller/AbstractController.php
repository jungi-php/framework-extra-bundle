<?php

namespace Jungi\FrameworkExtraBundle\Controller;

use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class AbstractController extends BaseAbstractController
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), array(
            'jungi.response_factory' => ResponseFactory::class,
            'serializer.normalizer' => '?'.NormalizerInterface::class
        ));
    }

    protected function entity($entity, int $status = 200, array $headers = []): Response
    {
        $factory = $this->container->get('jungi.response_factory');
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $factory->createEntityResponse($request, $entity, $status, $headers);
    }

    protected function normalizedEntity($entity, array $context, int $status = 200, array $headers = []): Response
    {
        if (!$this->container->has('serializer.normalizer')) {
            throw new \InvalidArgumentException('The "symfony/serializer" component is required for entity normalization.');
        }

        $normalizer = $this->container->get('serializer.normalizer');
        $factory = $this->container->get('jungi.response_factory');
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $normalizedEntity = $normalizer->normalize($entity, null, $context);

        return $factory->createEntityResponse($request, $normalizedEntity, $status, $headers);
    }
}
