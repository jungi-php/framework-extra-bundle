<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Controller\AbstractController;
use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterControllerAnnotationLocatorsPass;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RegisterControllerAnnotationLocatorsPassTest extends TestCase
{
    /** @test */
    public function controllerAnnotationLocatorsAreRegistered()
    {
        $container = new ContainerBuilder();
        $container->register('foo', FooController::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);

        $this->assertTrue($container->hasAlias('jungi.controller_annotation_locator'));

        $definition = $container->findDefinition('jungi.controller_annotation_locator');
        $locators = $definition->getArgument(0);

        $this->assertCount(6, $locators);
        $this->assertArrayHasKey('foo', $locators);
        $this->assertArrayHasKey('foo::withAnnotations', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$body', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$foo', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$bar', $locators);
        $this->assertArrayHasKey('foo::abstractAction$foo', $locators);

        $locator = $container->getDefinition((string)$locators['foo']->getValues()[0]);
        $this->assertLocatorDefinition([ResponseBody::class], $locator);

        $locator = $container->getDefinition((string)$locators['foo::withAnnotations']->getValues()[0]);
        $this->assertLocatorDefinition([ResponseBody::class], $locator);

        $locator = $container->getDefinition((string)$locators['foo::withAnnotations$body']->getValues()[0]);
        $this->assertLocatorDefinition([RequestBody::class], $locator);

        $locator = $container->getDefinition((string)$locators['foo::withAnnotations$foo']->getValues()[0]);
        $this->assertLocatorDefinition([QueryParam::class], $locator);

        $locator = $container->getDefinition((string)$locators['foo::withAnnotations$bar']->getValues()[0]);
        $this->assertLocatorDefinition([QueryParam::class], $locator);

        $locator = $container->getDefinition((string)$locators['foo::abstractAction$foo']->getValues()[0]);
        $this->assertLocatorDefinition([RequestParam::class], $locator);
    }

    /** @test */
    public function childDefinition(): void
    {
        $this->markTestSkipped('todo');
    }

    /** @test */
    public function duplicatedAnnotationOnClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation "%s" occurred more than once at class "%s".',
            ResponseBody::class,
            BadController::class
        ));

        $container = new ContainerBuilder();
        $container->register('foo', BadController::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);
    }

    /** @test */
    public function duplicatedAnnotationOnMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation "%s" occurred more than once at method "%s::bad()".',
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
            'Annotation "%s" occurred more than once at argument "%s::bad($foo)".',
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

    private function assertLocatorDefinition(array $expectedAnnotationClasses, Definition $locator): void
    {
        $this->assertEquals(SimpleContainer::class, $locator->getClass());
        $this->assertFalse($locator->isPublic());

        $annotations = $locator->getArgument(0);
        $this->assertCount(count($expectedAnnotationClasses), $annotations);

        foreach ($expectedAnnotationClasses as $annotationClass) {
            $this->assertArrayHasKey($annotationClass, $annotations);
            $this->assertInstanceOf(Definition::class, $annotations[$annotationClass]);
            $this->assertEquals($annotationClass, $annotations[$annotationClass]->getClass());
        }
    }
}

abstract class AbstractFooController extends AbstractController
{
    /** @QueryParam("foo") */
    abstract public function abstractAction(string $foo);
}

/**
 * @ForeignAnnotation
 * @ResponseBody
 */
class FooController extends AbstractFooController
{
    /**
     * @RequestBody("body")
     */
    public function __construct()
    {

    }

    /**
     * @ForeignAnnotation
     * @RequestBody("body")
     * @QueryParam("foo")
     * @QueryParam("bar")
     * @ResponseBody
     */
    public function withAnnotations(string $body, string $foo, string $bar)
    {
    }

    public function withNoAnnotations()
    {
    }

    /** @RequestParam("foo") */
    public function abstractAction(string $foo)
    {

    }

    /** @ResponseBody */
    protected function protectedAction()
    {

    }

    /** @ResponseBody */
    private function privateAction()
    {

    }
}

/**
 * @ResponseBody
 * @ResponseBody
 */
class BadController
{

}

class BadControllerMethod
{
    /**
     * @ResponseBody
     * @ResponseBody
     */
    public function bad()
    {

    }
}

class BadControllerArgument
{
    /**
     * @QueryParam("foo")
     * @QueryParam("foo")
     */
    public function bad($foo)
    {

    }
}

class BadControllerNonExistingArgument
{
    /**
     * @QueryParam("bar")
     */
    public function bad($foo)
    {

    }
}

/**
 * @Annotation
 */
class ForeignAnnotation
{
}
