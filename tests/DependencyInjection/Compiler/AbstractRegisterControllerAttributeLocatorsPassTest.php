<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection\Compiler;

use Jungi\FrameworkExtraBundle\DependencyInjection\Compiler\RegisterControllerAttributeLocatorsPass;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class AbstractRegisterControllerAttributeLocatorsPassTest extends TestCase
{
    protected static $attributeMap = array(
        'request_body' => '',
        'response_body' => '',
        'query_param' => '',
        'request_param' => '',
    );
    protected static $controllerMap = array(
        'foo' => '',
        'invokable' => '',
        'bad_controller_request_body_annotation_type' => '',
        'bad_controller_request_body_argument_type' => '',
    );

    /** @test */
    public function locatorsAreRegistered()
    {
        $container = new ContainerBuilder();
        $container->register('foo', static::$controllerMap['foo'])
            ->addTag('controller');

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition(RegisterControllerAttributeLocatorsPass::SERVICE_ALIAS);
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(5, $locators);
        $this->assertArrayHasKey('foo::withAttributes', $locators);
        $this->assertArrayHasKey('foo::withAttributes$body', $locators);
        $this->assertArrayHasKey('foo::withAttributes$foo', $locators);
        $this->assertArrayHasKey('foo::withAttributes$bar', $locators);
        $this->assertArrayHasKey('foo::abstractAction$foo', $locators);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes']->getValues()[0]);
        $this->assertLocatorDefinition([static::$attributeMap['response_body']], $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes$body']->getValues()[0]);
        $this->assertLocatorDefinition([static::$attributeMap['request_body']], $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes$foo']->getValues()[0]);
        $this->assertLocatorDefinition([static::$attributeMap['query_param']], $locator);

        $locator = $container->getDefinition((string) $locators['foo::withAttributes$bar']->getValues()[0]);
        $this->assertLocatorDefinition([static::$attributeMap['query_param']], $locator);

        $locator = $container->getDefinition((string) $locators['foo::abstractAction$foo']->getValues()[0]);
        $this->assertLocatorDefinition([static::$attributeMap['request_param']], $locator);
    }

    /** @test */
    public function locatorsAreRegisteredOnInvokable()
    {
        $container = new ContainerBuilder();
        $container->register('foo', static::$controllerMap['invokable'])
            ->addTag('controller');

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition(RegisterControllerAttributeLocatorsPass::SERVICE_ALIAS);
        $this->assertNotNull($definition);

        $locators = $definition->getArgument(0);

        $this->assertCount(2, $locators);
        $this->assertArrayHasKey('foo', $locators);
        $this->assertArrayHasKey('foo$body', $locators);

        $locator = $container->getDefinition((string) $locators['foo']->getValues()[0]);
        $this->assertLocatorDefinition([static::$attributeMap['response_body']], $locator);

        $locator = $container->getDefinition((string) $locators['foo$body']->getValues()[0]);
        $this->assertLocatorDefinition([static::$attributeMap['request_body']], $locator);
    }

    /** @test */
    public function locatorsAreRegisteredWithAliases()
    {
        $container = new ContainerBuilder();

        $container->register('foo', static::$controllerMap['foo'])
            ->addTag('controller');
        $container->register('invokable', static::$controllerMap['invokable'])
            ->addTag('controller');

        $container->setAlias('foo_alias', new Alias('foo', true));
        $container->setAlias('bar_alias', new Alias('foo', true));
        $container->setAlias('mee_alias', new Alias('invokable', true));
        $container->setAlias('zoo_alias', 'foo');

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition(RegisterControllerAttributeLocatorsPass::SERVICE_ALIAS);
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

        $container->register('parent', static::$controllerMap['foo']);
        $container->setDefinition('child', new ChildDefinition('parent'))
            ->addTag('controller');

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition(RegisterControllerAttributeLocatorsPass::SERVICE_ALIAS);
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

        $container->setParameter('controller_class', static::$controllerMap['foo']);
        $container->register('foo', '%controller_class%')
            ->addTag('controller');

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);

        $definition = $container->findDefinition(RegisterControllerAttributeLocatorsPass::SERVICE_ALIAS);
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

        $pass = $this->createCompilerPass('controller');
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

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);
    }

    /** @test */
    public function invalidRequestBodyArgumentType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected the argument "foo" to be of "string[]" type, got "string" in "%s::bad()"',
            static::$controllerMap['bad_controller_request_body_argument_type']
        ));

        $container = new ContainerBuilder();
        $container->register('foo', static::$controllerMap['bad_controller_request_body_argument_type'])
            ->addTag('controller');

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);
    }

    /** @test */
    public function invalidRequestBodyAnnotationType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected the argument "foo" to be annotated as a collection type, got "string" in "%s::bad()"',
            static::$controllerMap['bad_controller_request_body_annotation_type']
        ));

        $container = new ContainerBuilder();
        $container->register('foo', static::$controllerMap['bad_controller_request_body_annotation_type'])
            ->addTag('controller');

        $pass = $this->createCompilerPass('controller');
        $pass->process($container);
    }

    abstract protected function createCompilerPass(string $controllerTag): CompilerPassInterface;

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

