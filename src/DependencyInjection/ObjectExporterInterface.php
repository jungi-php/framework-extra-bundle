<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

/**
 * Exports an object to the service definition.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
interface ObjectExporterInterface
{
    public function export(object $object): Definition;
}
