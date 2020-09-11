<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterControllerAnnotationLocatorsPass;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerArgument;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerMethod;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerNonExistingArgument;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerRequestBodyAnnotationType;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerRequestBodyArgumentType;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\FooController;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\InvokableController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RegisterControllerAnnotationLocatorsPassTest extends AbstractRegisterControllerAttributeLocatorsPassTest
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

    /** @test */
    public function duplicatedAnnotationOnMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation "%s" occurred more than once at "%s::bad()".',
            ResponseBody::class,
            BadControllerMethod::class
        ));

        $container = new ContainerBuilder();
        $container->register('foo', BadControllerMethod::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);
    }

    /** @test */
    public function duplicatedAnnotationOnArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation "%s" occurred more than once for the argument "foo" at "%s::bad()".',
            QueryParam::class,
            BadControllerArgument::class
        ));

        $container = new ContainerBuilder();
        $container->register('foo', BadControllerArgument::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);
    }

    /** @test */
    public function argumentDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected to have the argument "bar" in "%s::bad()"',
            BadControllerNonExistingArgument::class
        ));

        $container = new ContainerBuilder();
        $container->register('foo', BadControllerNonExistingArgument::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);
    }

    protected function createCompilerPass(string $controllerTag): CompilerPassInterface
    {
        return new RegisterControllerAnnotationLocatorsPass($controllerTag);
    }
}
