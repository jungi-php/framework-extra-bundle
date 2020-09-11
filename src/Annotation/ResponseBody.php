<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\Attribute\ResponseBody as BaseResponseBody;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class ResponseBody extends BaseResponseBody implements Annotation
{
}
