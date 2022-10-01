<?php

namespace Jungi\FrameworkExtraBundle\Controller;

use Jungi\FrameworkExtraBundle\Http\EntityResponse;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
trait ControllerTrait
{
    /**
     * Returns an EntityResponse with the given entity that is mapped
     * to the selected content type using the content negotiation.
     */
    protected function entity(mixed $entity, int $status = 200, array $headers = []): EntityResponse
    {
        return new EntityResponse($entity, $status, $headers);
    }
}
