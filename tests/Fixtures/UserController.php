<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\QueryParams;
use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ResponseBody
 */
class UserController
{
    public function getUserResidentialAddress(string $userId)
    {
        return new UserResidentialAddressResource('street', 'city', 'province', 'country_code');
    }

    /**
     * @QueryParam("limit")
     */
    public function getUsers(int $limit)
    {
        return [];
    }

    /**
     * @QueryParams("data")
     */
    public function filterUsers(FilterUserData $data)
    {
        return [];
    }

    /**
     * @RequestBody("resource")
     */
    public function changeResidentialAddress(string $userId, UserResidentialAddressResource $resource)
    {
        return new Response('', 204);
    }
}
