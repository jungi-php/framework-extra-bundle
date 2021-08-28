<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterControllerAnnotationLocatorsPass;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerArgument;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerMethod;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerNonExistingArgument;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerRequestBodyAnnotationType;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\BadControllerRequestBodyArgumentType;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\FooController;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\Annotation\InvokableController;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
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

        $definition = $container->findDefinition(RegisterControllerAnnotationLocatorsPass::SERVICE_ALIAS);
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(5, $locators);
        $this->assertArrayHasKey('foo::withAttributes', $locators);
        $this->assertArrayHasKey('foo::withAttributes$body', $locators);
        $this->assertArrayHasKey('foo::withAttributes$foo', $locators);
        $this->assertArrayHasKey('foo::withAttributes$bar', $locators);
        $this->assertArrayHasKey('foo::abstractAction$foo', $locators);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes']->getValues()[0]);
        $this->assertLocatorDefinition(ResponseBody::class, $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes$body']->getValues()[0]);
        $this->assertLocatorDefinition(RequestBody::class, $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes$foo']->getValues()[0]);
        $this->assertLocatorDefinition(QueryParam::class, $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes$bar']->getValues()[0]);
        $this->assertLocatorDefinition(QueryParam::class, $locator);

        $locator = $container->getDefinition((string) $locators['foo::abstractAction$foo']->getValues()[0]);
        $this->assertLocatorDefinition(RequestParam::class, $locator);
    }

    /** @test */
    public function locatorsAreRegisteredOnInvokable()
    {
        $container = new ContainerBuilder();
        $container->register('foo', InvokableController::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition(RegisterControllerAnnotationLocatorsPass::SERVICE_ALIAS);
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(2, $locators);
        $this->assertArrayHasKey('foo', $locators);
        $this->assertArrayHasKey('foo$body', $locators);

        $locator = $container->getDefinition((string) $locators['foo']->getValues()[0]);
        $this->assertLocatorDefinition(ResponseBody::class, $locator);

        $locator = $container->getDefinition((string) $locators['foo$body']->getValues()[0]);
        $this->assertLocatorDefinition(RequestBody::class, $locator);
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

        $definition = $container->findDefinition(RegisterControllerAnnotationLocatorsPass::SERVICE_ALIAS);
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(19, $locators);
        $this->assertArrayHasKey('invokable', $locators);
        $this->assertArrayHasKey('invokable$body', $locators);
        $this->assertArrayHasKey('mee_alias', $locators);
        $this->assertArrayHasKey('mee_alias$body', $locators);
        $this->assertArrayHasKey('foo::withAttributes', $locators);
        $this->assertArrayHasKey('foo::withAttributes$body', $locators);
        $this->assertArrayHasKey('foo::withAttributes$foo', $locators);
        $this->assertArrayHasKey('foo::withAttributes$bar', $locators);
        $this->assertArrayHasKey('foo::abstractAction$foo', $locators);
        $this->assertArrayHasKey('foo_alias::withAttributes', $locators);
        $this->assertArrayHasKey('foo_alias::withAttributes$body', $locators);
        $this->assertArrayHasKey('foo_alias::withAttributes$foo', $locators);
        $this->assertArrayHasKey('foo_alias::withAttributes$bar', $locators);
        $this->assertArrayHasKey('foo_alias::abstractAction$foo', $locators);
        $this->assertArrayHasKey('bar_alias::withAttributes', $locators);
        $this->assertArrayHasKey('bar_alias::withAttributes$body', $locators);
        $this->assertArrayHasKey('bar_alias::withAttributes$foo', $locators);
        $this->assertArrayHasKey('bar_alias::withAttributes$bar', $locators);
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

        $definition = $container->findDefinition(RegisterControllerAnnotationLocatorsPass::SERVICE_ALIAS);
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(5, $locators);
        $this->assertArrayHasKey('child::withAttributes', $locators);
        $this->assertArrayHasKey('child::withAttributes$body', $locators);
        $this->assertArrayHasKey('child::withAttributes$foo', $locators);
        $this->assertArrayHasKey('child::withAttributes$bar', $locators);
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

        $definition = $container->findDefinition(RegisterControllerAnnotationLocatorsPass::SERVICE_ALIAS);
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(5, $locators);
        $this->assertArrayHasKey('foo::withAttributes', $locators);
        $this->assertArrayHasKey('foo::withAttributes$body', $locators);
        $this->assertArrayHasKey('foo::withAttributes$foo', $locators);
        $this->assertArrayHasKey('foo::withAttributes$bar', $locators);
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
    public function invalidRequestBodyArgumentType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected the argument "foo" to be of "string[]" type, got "string" in "%s::bad()"',
            BadControllerRequestBodyArgumentType::class
        ));

        $container = new ContainerBuilder();
        $container->register('foo', BadControllerRequestBodyArgumentType::class)
            ->addTag('controller');

        $pass = new RegisterControllerAnnotationLocatorsPass('controller');
        $pass->process($container);
    }

    /** @test */
    public function invalidRequestBodyAnnotationType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected the argument "foo" to be annotated as a collection type, got "string" in "%s::bad()"',
            BadControllerRequestBodyAnnotationType::class
        ));

        $container = new ContainerBuilder();
        $container->register('foo', BadControllerRequestBodyAnnotationType::class)
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
            'Argument "foo" has more than one annotation at "%s::bad()".',
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

    private function assertLocatorDefinition(string $expectedAnnotationClass, Definition $locator): void
    {
        $expectedAttributeClass = get_parent_class($expectedAnnotationClass);

        $this->assertEquals(SimpleContainer::class, $locator->getClass());
        $this->assertFalse($locator->isPublic());

        $attributes = $locator->getArgument(0);

        $this->assertCount(1, $attributes);
        $this->assertArrayHasKey($expectedAttributeClass, $attributes);
        $this->assertInstanceOf(Definition::class, $attributes[$expectedAttributeClass]);
        $this->assertEquals($expectedAnnotationClass, $attributes[$expectedAttributeClass]->getClass());
    }
}
