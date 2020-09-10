<?php

namespace Jungi\FrameworkExtraBundle\Mapper;

use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ConverterMapperAdapter implements MapperInterface
{
    private $mapDataToType;
    private $converter;

    public function __construct(string $mapDataToType, ConverterInterface $converter)
    {
        $this->mapDataToType = $mapDataToType;
        $this->converter = $converter;
    }

    public function mapFrom(string $data, string $type)
    {
        try {
            return $this->converter->convert($data, $type);
        } catch (TypeConversionException $e) {
            throw new MalformedDataException($e->getMessage(), null, $e);
        }
    }

    public function mapTo($data): string
    {
        return $this->converter->convert($data, $this->mapDataToType);
    }
}
