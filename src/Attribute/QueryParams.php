<?php

namespace Jungi\FrameworkExtraBundle\Attribute;

use Attribute as PhpAttribute;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exportable;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @final
 */
#[PhpAttribute(PhpAttribute::TARGET_PARAMETER)]
class QueryParams implements Attribute, Exportable
{

}
