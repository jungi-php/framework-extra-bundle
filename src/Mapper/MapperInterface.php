<?php

namespace Jungi\FrameworkExtraBundle\Mapper;

/**
 * Maps data from a text representation to a desired type and vice versa.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface MapperInterface
{
    /**
     * Maps from text data to type.
     *
     * @throws \InvalidArgumentException On non supported data
     * @throws MalformedDataException
     */
    public function mapFrom(string $data, string $type): mixed;

    /**
     * Maps PHP data to text.
     *
     * @throws \InvalidArgumentException On non supported data
     */
    public function mapTo(mixed $data): string;
}
