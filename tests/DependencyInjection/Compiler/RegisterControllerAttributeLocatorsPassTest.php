<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use Jungi\FrameworkExtraBundle\Attribute\ResponseBody;
use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterControllerAttributeLocatorsPass;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute\BadControllerRequestBodyAnnotationType;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute\BadControllerRequestBodyArgumentType;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute\FooController;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Attribute\InvokableController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @requires PHP 8
 */
class RegisterControllerAttributeLocatorsPassTest extends AbstractRegisterControllerAttributeLocatorsPassTest
{
    protected static $attributeMap = array(
        'request_body' => RequestBody::class,
        'response_body' => ResponseBody::class,
        'query_param' => QueryParam::class,
        'request_param' => RequestParam::class,
    );
    protected static $controllerMap = array(
        'foo' => FooController::class,
        'invokable' => InvokableController::class,
        'bad_controller_request_body_annotation_type' => BadControllerRequestBodyAnnotationType::class,
        'bad_controller_request_body_argument_type' => BadControllerRequestBodyArgumentType::class,
    );

    protected function createCompilerPass(string $controllerTag): CompilerPassInterface
    {
        return new RegisterControllerAttributeLocatorsPass($controllerTag);
    }
}
