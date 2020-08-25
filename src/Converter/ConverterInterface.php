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
     * @param mixed $data
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException On non supported type
     * @throws TypeConversionException
     */
    public function convert($data, string $type);
}
