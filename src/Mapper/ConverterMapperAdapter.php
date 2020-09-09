<?php

namespace Jungi\FrameworkExtraBundle\Mapper;

use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;

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
        return $this->converter->convert($data, $type);
    }

    public function mapTo($data): string
    {
        return $this->converter->convert($data, $this->mapDataToType);
    }
}
