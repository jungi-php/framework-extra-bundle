<?php

namespace Jungi\FrameworkExtraBundle\Tests\Serializer;

use Jungi\FrameworkExtraBundle\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class SerializerTest extends TestCase
{
    /** @test */
    public function denormalizationOnSameDataType(): void
    {
        $serializer = new Serializer();

        $data = new \stdClass();
        $this->assertTrue($serializer->supportsDenormalization($data, 'stdClass'));
        $this->assertFalse($serializer->supportsDenormalization($data, 'array'));
        $this->assertSame($data, $serializer->denormalize($data, 'stdClass'));
    }

    /** @test */
    public function unsupportedDenormalization(): void
    {
        $this->expectException(NotNormalizableValueException::class);

        $serializer = new Serializer();
        $serializer->denormalize(new \stdClass(), 'string');
    }
}
