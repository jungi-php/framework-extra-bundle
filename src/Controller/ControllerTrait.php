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
    /**
     * @param mixed $entity
     * @param int   $status
     * @param array $headers
     *
     * @return Response
     */
    protected function entity($entity, int $status = 200, array $headers = []): Response
    {
        $factory = $this->container->get('jungi.response_factory');
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $factory->createEntityResponse($request, $entity, $status, $headers);
    }
}
