<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ResponseBody
 */
class UserController
{
    public function getResidentialAddress()
    {
        return new UserResidentialAddressResource('street', 'city', 'province', 'country_code');
    }

    /**
     * @RequestBody("resource")
     */
    public function changeResidentialAddress(string $userId, UserResidentialAddressResource $resource)
    {
        return new Response('', 204);
    }
}
