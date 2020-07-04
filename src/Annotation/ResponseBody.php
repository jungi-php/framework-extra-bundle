<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\DependencyInjection\Exportable;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class ResponseBody implements AnnotationInterface, Exportable
{
}
