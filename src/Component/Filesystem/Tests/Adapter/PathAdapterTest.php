<?php

namespace Pagekit\Component\Filesystem\Tests\Adapter;

use Pagekit\Component\Filesystem\File;
use Pagekit\Component\Filesystem\Adapter\PathAdapter;

class TempAdapterTest extends \PHPUnit_Framework_TestCase
{
    use \Pagekit\Tests\FileUtil;

    protected $fixtures;
    protected $workspace;

    public function setUp()
    {
        $this->fixtures  = dirname(__DIR__).'/Fixtures';
        $this->workspace = $this->getTempDir('filesystem_');

        File::registerAdapter('temp', new PathAdapter($this->workspace));
    }

    public function tearDown()
    {
        $this->removeDir($this->workspace);
    }

    public function testCopyFile()
    {
        $file1 = $this->fixtures.'/file1.txt';

        $this->assertTrue(File::copy($file1, 'temp://file1.txt'));
        $this->assertTrue(File::exists('temp://file1.txt'));
    }

    public function testCopyFileNotFound()
    {
        $file3 = $this->fixtures.'/file3.txt';

        $this->assertFalse(File::exists($file3));
        $this->assertFalse(File::copy($file3, 'temp://file3.txt'));
    }

    public function testCopyDir()
    {
        $this->assertTrue(File::copyDir($this->fixtures, 'temp://'));
        $this->assertTrue(File::exists('temp://file1.txt'));
        $this->assertTrue(File::exists('temp://file2.txt'));
    }

    public function testCopyDirNotFound()
    {
        $dir = __DIR__.'/Directory';

        $this->assertFalse(File::exists($dir));
        $this->assertFalse(File::copyDir($dir, 'temp://'));
    }

    public function testDeleteFile()
    {
        $file1 = $this->fixtures.'/file1.txt';

        $this->assertTrue(File::copy($file1, 'temp://file1.txt'));
        $this->assertTrue(File::delete('temp://file1.txt'));
        $this->assertFalse(File::exists('temp://file1.txt'));
    }

    public function testDeleteFileNotFound()
    {
        $file3 = 'temp://file3.txt';

        $this->assertFalse(File::exists($file3));
        $this->assertFalse(File::delete($file3));
    }

    public function testDeleteDir()
    {
        $dir = 'temp://Directory';

        $this->assertTrue(File::copyDir($this->fixtures, $dir));
        $this->assertTrue(File::delete($dir));
        $this->assertFalse(File::exists($dir));
    }
}
