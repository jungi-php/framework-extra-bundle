<?php

namespace Jungi\FrameworkExtraBundle\Tests\Util;

use Jungi\FrameworkExtraBundle\Util\TmpFileUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class TmpFileUtilsTest extends TestCase
{
    /** @test */
    public function data()
    {
        $content = 'hello,world,123,1.23';

        $file = TmpFileUtils::fromData($content);
        $handle = fopen($file, 'rb');

        $this->assertEquals($content, stream_get_contents($handle));

        // clear tmpfile references to remove it from the filesystem
        TmpFileUtils::removeReferences();

        $this->assertFileNotExists($file);
    }

    /** @test */
    public function rename()
    {
        $content = 'hello-world';

        $file = TmpFileUtils::fromData($content);
        $handle = fopen($file, 'rb');

        $movedFile = tempnam(sys_get_temp_dir(), 'foo');
        rename($file, $movedFile);

        $this->assertFileNotExists($file);

        TmpFileUtils::removeReferences();

        $this->assertFileExists($movedFile);
        $this->assertEquals($content, stream_get_contents($handle));

        unlink($movedFile);
    }

    /** @test */
    public function copy()
    {
        $content = 'hello-world';

        $file = TmpFileUtils::fromData($content);
        $handle = fopen($file, 'rb');

        $copiedFile = tempnam(sys_get_temp_dir(), 'foo');
        copy($file, $copiedFile);

        $this->assertFileExists($file);

        TmpFileUtils::removeReferences();

        $this->assertFileExists($copiedFile);
        $this->assertEquals($content, stream_get_contents($handle));

        unlink($copiedFile);
    }
}
