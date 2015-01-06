<?php

namespace Pagekit\Component\Filesystem\Tests;

use Pagekit\Component\Filesystem\File;
use Pagekit\Component\Filesystem\Adapter\FileAdapter;

class FileTest extends \PHPUnit_Framework_TestCase
{
    use \Pagekit\Tests\FileUtil;

    protected $fixtures;
    protected $workspace;

    public function setUp()
    {
        $this->fixtures = __DIR__.'/Fixtures';
        $this->workspace = $this->getTempDir('filesystem_');

        File::registerAdapter('file', new FileAdapter(__DIR__, 'http://localhost'));
    }

    public function tearDown()
    {
        $this->removeDir($this->workspace);
    }

    public function testGetUrlLocal()
    {
        $this->assertSame('/Fixtures', File::getUrl($this->fixtures));
        $this->assertSame('//localhost/Fixtures', File::getUrl($this->fixtures, 'network'));
        $this->assertSame('http://localhost/Fixtures', File::getUrl($this->fixtures, true));
    }

    public function testGetUrlExternal()
    {
        $ftp  = 'ftp://example.com';
        $http = 'http://username:password@example.com/path?arg=value#anchor';

        $this->assertSame('/', File::getUrl($ftp));
        $this->assertSame('//example.com', File::getUrl($ftp, 'network'));
        $this->assertSame($ftp, File::getUrl($ftp, true));

        $this->assertSame('/path?arg=value#anchor', File::getUrl($http));
        $this->assertSame('//username:password@example.com/path?arg=value#anchor', File::getUrl($http, 'network'));
        $this->assertSame($http, File::getUrl($http, true));
    }

    public function testGetUrlNotFound()
    {
        $dir = __DIR__.'/Directory';

        $this->assertFalse(File::getUrl($dir));
    }

    public function testGetPath()
    {
        $this->assertSame('/file1.txt', File::getPath('/file1.txt'));
    }

    public function testExists()
    {
        $file1 = $this->fixtures.'/file1.txt';
        $file2 = $this->fixtures.'/file2.txt';
        $file3 = $this->fixtures.'/file3.txt';

        $this->assertTrue(File::exists($file1));
        $this->assertTrue(File::exists($file2));
        $this->assertTrue(File::exists([$file1, $file2]));
        $this->assertFalse(File::exists($file3));
        $this->assertFalse(File::exists([$file1, $file2, $file3]));
    }

    public function testCopyFile()
    {
        $file1 = $this->fixtures.'/file1.txt';

        $this->assertTrue(File::copy($file1, $this->workspace.'/file1.txt'));
        $this->assertTrue(File::exists($this->workspace.'/file1.txt'));
    }

    public function testCopyFileNotFound()
    {
        $file3 = $this->fixtures.'/file3.txt';

        $this->assertFalse(File::exists($file3));
        $this->assertFalse(File::copy($file3, $this->workspace.'/file3.txt'));
    }

    public function testCopyDir()
    {
        $this->assertTrue(File::copyDir($this->fixtures, $this->workspace));
        $this->assertTrue(File::exists($this->workspace.'/file1.txt'));
        $this->assertTrue(File::exists($this->workspace.'/file2.txt'));
    }

    public function testCopyDirNotFound()
    {
        $dir = __DIR__.'/Directory';

        $this->assertFalse(File::exists($dir));
        $this->assertFalse(File::copyDir($dir, $this->workspace));
    }

    public function testDeleteFile()
    {
        $file1 = $this->fixtures.'/file1.txt';

        $this->assertTrue(File::copy($file1, $this->workspace.'/file1.txt'));
        $this->assertTrue(File::delete($this->workspace.'/file1.txt'));
        $this->assertFalse(File::exists($this->workspace.'/file1.txt'));
    }

    public function testDeleteFileNotFound()
    {
        $file3 = $this->workspace.'/file3.txt';

        $this->assertFalse(File::exists($file3));
        $this->assertFalse(File::delete($file3));
    }

    public function testDeleteDir()
    {
        $dir = $this->workspace.'/Directory';

        $this->assertTrue(File::copyDir($this->fixtures, $dir));
        $this->assertTrue(File::delete($dir));
        $this->assertFalse(File::exists($dir));
    }

    public function testListDir()
    {
        $this->assertContains('file1.txt', File::listDir($this->fixtures));
        $this->assertContains('file2.txt', File::listDir($this->fixtures));
    }
}
