<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

/**
 * An object with state that can be exported to the service container.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface StatefulObject extends ExportableObject
{
    /**
     * @param array $data
     *
     * @return static
     */
    public static function fromState(array $data);
}
