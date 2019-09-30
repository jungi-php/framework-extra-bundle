<?php

namespace Jungi\FrameworkExtraBundle\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    protected function normalizeEntity($entity, array $context, int $status = 200, array $headers = []): Response
    {
        if (!$this->container->has('serializer')) {
            throw new \InvalidArgumentException('The Serializer component is required.');
        }

        $serializer = $this->container->get('serializer');
        if (!$serializer instanceof NormalizerInterface) {
            throw new \UnexpectedValueException(sprintf('Expected a serializer that implements NormalizerInterface.'));
        }

        $factory = $this->container->get('jungi.response_factory');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $normalizedEntity = $serializer->normalize($entity, null, $context);

        return $factory->createEntityResponse($request, $normalizedEntity, $status, $headers);
    }
}
