<?php

namespace Jungi\FrameworkExtraBundle\Mapper;

/**
 * Maps data from a text representation to an object and vice versa.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface MapperInterface
{
    /**
     * Maps from data to type.
     *
     * @param string $data
     * @param string $type
     *
     * @return object
     *
     * @throws \InvalidArgumentException On non supported data
     * @throws MalformedDataException
     */
    public function mapFrom(string $data, string $type): object;

    /**
     * Maps data to type.
     *
     * @param mixed $data
     *
     * @return string
     *
     * @throws \InvalidArgumentException On non supported data
     */
    public function mapTo($data): string;
}