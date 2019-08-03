<?php

namespace Jungi\FrameworkExtraBundle\Http\Conversion\Mapper;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface MapperInterface
{
    /**
     * @param string $data
     * @param string $type
     *
     * @return object
     *
     * @throws \InvalidArgumentException On non supported data
     * @throws MalformedDataException
     */
    public function mapFromData(string $data, string $type): object;

    /**
     * @param mixed $data
     *
     * @return string
     *
     * @throws \InvalidArgumentException On non supported data
     */
    public function mapDataTo($data): string;
}
