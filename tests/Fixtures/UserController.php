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
        return new UserResidentialAddressRepresentation('street', 'city', 'province', 'country_code');
    }

    /**
     * @RequestBody("cmd")
     */
    public function changeResidentialAddress(ChangeUserResidentialAddressCommand $cmd)
    {
        return new Response('', 204);
    }
}
