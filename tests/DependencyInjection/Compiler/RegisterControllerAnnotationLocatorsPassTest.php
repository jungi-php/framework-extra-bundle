<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterControllerAnnotationLocatorsPass;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RegisterControllerAnnotationLocatorsPassTest extends TestCase
{
    /** @test */
    public function locatorsAreRegistered()
    {
        $container = new ContainerBuilder();
        $container->register('foo', FooController::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition('jungi.controller_annotation_locator');
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(5, $locators);
        $this->assertArrayHasKey('foo::withAnnotations', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$body', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$foo', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$bar', $locators);
        $this->assertArrayHasKey('foo::abstractAction$foo', $locators);

        $locator = $container->getDefinition((string) $locators['foo::withAnnotations']->getValues()[0]);
        $this->assertLocatorDefinition([ResponseBody::class], $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAnnotations$body']->getValues()[0]);
        $this->assertLocatorDefinition([RequestBody::class], $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAnnotations$foo']->getValues()[0]);
        $this->assertLocatorDefinition([QueryParam::class], $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAnnotations$bar']->getValues()[0]);
        $this->assertLocatorDefinition([QueryParam::class], $locator);

        $locator = $container->getDefinition((string) $locators['foo::abstractAction$foo']->getValues()[0]);
        $this->assertLocatorDefinition([RequestParam::class], $locator);
    }

    /** @test */
    public function locatorsAreRegisteredOnInvokable()
    {
        $container = new ContainerBuilder();
        $container->register('foo', InvokableController::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition('jungi.controller_annotation_locator');
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(2, $locators);
        $this->assertArrayHasKey('foo', $locators);
        $this->assertArrayHasKey('foo$body', $locators);

        $locator = $container->getDefinition((string) $locators['foo']->getValues()[0]);
        $this->assertLocatorDefinition([ResponseBody::class], $locator);

        $locator = $container->getDefinition((string) $locators['foo$body']->getValues()[0]);
        $this->assertLocatorDefinition([RequestBody::class], $locator);
    }

    /** @test */
    public function locatorsAreRegisteredWithAliases()
    {
        $container = new ContainerBuilder();

        $container->register('foo', FooController::class)
            ->addTag('controller');
        $container->register('invokable', InvokableController::class)
            ->addTag('controller');

        $container->setAlias('foo_alias', new Alias('foo', true));
        $container->setAlias('bar_alias', new Alias('foo', true));
        $container->setAlias('mee_alias', new Alias('invokable', true));
        $container->setAlias('zoo_alias', 'foo');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition('jungi.controller_annotation_locator');
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(19, $locators);
        $this->assertArrayHasKey('invokable', $locators);
        $this->assertArrayHasKey('invokable$body', $locators);
        $this->assertArrayHasKey('mee_alias', $locators);
        $this->assertArrayHasKey('mee_alias$body', $locators);
        $this->assertArrayHasKey('foo::withAnnotations', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$body', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$foo', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$bar', $locators);
        $this->assertArrayHasKey('foo::abstractAction$foo', $locators);
        $this->assertArrayHasKey('foo_alias::withAnnotations', $locators);
        $this->assertArrayHasKey('foo_alias::withAnnotations$body', $locators);
        $this->assertArrayHasKey('foo_alias::withAnnotations$foo', $locators);
        $this->assertArrayHasKey('foo_alias::withAnnotations$bar', $locators);
        $this->assertArrayHasKey('foo_alias::abstractAction$foo', $locators);
        $this->assertArrayHasKey('bar_alias::withAnnotations', $locators);
        $this->assertArrayHasKey('bar_alias::withAnnotations$body', $locators);
        $this->assertArrayHasKey('bar_alias::withAnnotations$foo', $locators);
        $this->assertArrayHasKey('bar_alias::withAnnotations$bar', $locators);
        $this->assertArrayHasKey('bar_alias::abstractAction$foo', $locators);
    }

    /** @test */
    public function locatorsAreRegisteredFromChildDefinition(): void
    {
        $container = new ContainerBuilder();

        $container->register('parent', FooController::class);
        $container->setDefinition('child', new ChildDefinition('parent'))
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition('jungi.controller_annotation_locator');
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(5, $locators);
        $this->assertArrayHasKey('child::withAnnotations', $locators);
        $this->assertArrayHasKey('child::withAnnotations$body', $locators);
        $this->assertArrayHasKey('child::withAnnotations$foo', $locators);
        $this->assertArrayHasKey('child::withAnnotations$bar', $locators);
        $this->assertArrayHasKey('child::abstractAction$foo', $locators);
    }

    /** @test */
    public function locatorsAreRegisteredUsingClassFromParameters(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter('controller_class', FooController::class);
        $container->register('foo', '%controller_class%')
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition('jungi.controller_annotation_locator');
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(5, $locators);
        $this->assertArrayHasKey('foo::withAnnotations', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$body', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$foo', $locators);
        $this->assertArrayHasKey('foo::withAnnotations$bar', $locators);
        $this->assertArrayHasKey('foo::abstractAction$foo', $locators);
    }

    /** @test */
    public function invalidClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Class "%s" used for service "foo" cannot be found.', NotExistingController::class));

        $container = new ContainerBuilder();

        $container->register('foo', NotExistingController::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);
    }

    /** @test */
    public function invalidClassFromParameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Class "%s" used for service "foo" cannot be found.', NotExistingController::class));

        $container = new ContainerBuilder();

        $container->setParameter('controller_class', NotExistingController::class);
        $container->register('foo', '%controller_class%')
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);
    }

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

class InvokableController extends AbstractController
{
    /**
     * @RequestBody("body")
     * @ResponseBody
     */
    public function __invoke(string $body)
    {

    }
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
