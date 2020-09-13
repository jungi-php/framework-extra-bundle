<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

use Attribute as PhpAttribute;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exportable;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @final-public should be treated as final outside the library, extended only by the annotation
 */
#[PhpAttribute(PhpAttribute::TARGET_PARAMETER)]
class QueryParams implements Attribute, Exportable
{
}
