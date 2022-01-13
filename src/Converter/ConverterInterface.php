<?php

namespace Jungi\FrameworkExtraBundle\Converter;

/**
 * Provides type conversion.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface ConverterInterface
{
    /**
     * Converts data to type.
     *
     * @throws \InvalidArgumentException On non supported type
     * @throws TypeConversionException
     */
    public function convert(mixed $data, string $type): mixed;
}
