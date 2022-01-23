<?php

namespace Jungi\FrameworkExtraBundle\Tests\Serializer;

use Jungi\FrameworkExtraBundle\Serializer\ObjectAlreadyOfTypeDenormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ObjectAlreadyOfTypeDenormalizerTest extends TestCase
{
    /** @test */
    public function supports(): void
    {
        $denormalizer = new ObjectAlreadyOfTypeDenormalizer();

        $this->assertTrue($denormalizer->supportsDenormalization(new A(), A::class));
        $this->assertFalse($denormalizer->supportsDenormalization(new B(), A::class));
        $this->assertFalse($denormalizer->supportsDenormalization('foo', 'string'));
    }

    /** @test */
    public function denormalize(): void
    {
        $denormalizer = new ObjectAlreadyOfTypeDenormalizer();
        $expected = new A();

        $this->assertSame($expected, $denormalizer->denormalize($expected, A::class));
    }

    /** @test */
    public function denormalizeOnNonObjectTypeFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data must be of object type, given "string".');

        $denormalizer = new ObjectAlreadyOfTypeDenormalizer();
        $denormalizer->denormalize('foo', 'string');
    }

    /** @test */
    public function denormalizeOnDifferentClassTypeFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Data expected to be of type "%s", given "%s".', A::class, B::class));

        $denormalizer = new ObjectAlreadyOfTypeDenormalizer();
        $denormalizer->denormalize(new B(), A::class);
    }
}

class A {

}

class B extends A {

}