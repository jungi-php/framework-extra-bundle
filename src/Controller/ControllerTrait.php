<?php

namespace Jungi\FrameworkExtraBundle\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @property ContainerInterface $container
 */
trait ControllerTrait
{
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
