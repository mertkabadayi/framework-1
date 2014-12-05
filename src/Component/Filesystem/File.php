<?php

namespace Pagekit\Component\Filesystem;

use Pagekit\Component\Filesystem\Adapter\AdapterInterface;

class File
{
    protected static $adapters = [];

    /**
     * Gets file URL.
     *
     * @param  string $file
     * @return string|false
     */
    public static function getUrl($file)
    {
        return self::getPathInfo($file, 'url') ?: false;
    }

    /**
     * Gets file path.
     *
     * @param  string $file
     * @return string|false
     */
    public static function getPath($file)
    {
        return self::getPathInfo($file, 'pathname') ?: false;
    }

    /**
     * Gets file path info.
     *
     * @param  string $file
     * @param  string $option
     * @return string|array
     */
    public static function getPathInfo($file, $option = null)
    {
        $info = Path::parse($file);

        if ($info['protocol'] != 'file') {
            $info['url'] = $info['pathname'];
        }

        if ($adapter = self::getAdapter($info['protocol'])) {
            $info = $adapter->parsePathInfo($info);
        }

        if ($option === null) {
            return $info;
        }

        return array_key_exists($option, $info) ? $info[$option] : '';
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @param  string|array $files
     * @return bool
     */
    public static function exists($files)
    {
        $files = (array) $files;

        foreach ($files as $file) {

            $file = self::getPathInfo($file, 'pathname');

            if (!file_exists($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Copies a file.
     *
     * @param  string $source
     * @param  string $target
     * @return bool
     */
    public static function copy($source, $target)
    {
        $source = self::getPathInfo($source);
        $target = self::getPathInfo($target);

        if (!is_file($source['pathname']) || !self::makeDir($target['root'].dirname($target['path']))) {
            return false;
        }

        return @copy($source['pathname'], $target['pathname']);
    }

    /**
     * Deletes a file.
     *
     * @param  string|array $files
     * @return bool
     */
    public static function delete($files)
    {
        $files = (array) $files;

        foreach ($files as $file) {

            $file = self::getPathInfo($file);

            if (is_dir($file['pathname'])) {

                if ($file['path'] !== '') {
                    $file['pathname'] .= '/';
                }

                foreach (self::listDir($file['pathname']) as $name) {
                    if (!self::delete($file['pathname'].$name)) {
                        return false;
                    }
                }

                if (!@rmdir($file['pathname'])) {
                    return false;
                }

            } elseif (!@unlink($file['pathname'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * List files and directories inside the specified path.
     *
     * @param  string $dir
     * @return array
     */
    public static function listDir($dir)
    {
        $dir = self::getPathInfo($dir, 'pathname');

        return array_diff(scandir($dir) ?: [], ['..', '.']);
    }

    /**
     * Makes a directory.
     *
     * @param  string $dir
     * @param  int    $mode
     * @param  bool   $recursive
     * @return bool
     */
    public static function makeDir($dir, $mode = 0777, $recursive = true)
    {
        $dir = self::getPathInfo($dir, 'pathname');

        return is_dir($dir) ? true : @mkdir($dir, $mode, $recursive);
    }

    /**
     * Copies a directory.
     *
     * @param  string $source
     * @param  string $target
     * @return bool
     */
    public static function copyDir($source, $target)
    {
        $source = self::getPathInfo($source);
        $target = self::getPathInfo($target);

        if (!is_dir($source['pathname']) || !self::makeDir($target['pathname'])) {
            return false;
        }

        if ($source['path'] !== '') {
            $source['pathname'] .= '/';
        }

        if ($target['path'] !== '') {
            $target['pathname'] .= '/';
        }

        foreach (self::listDir($source['pathname']) as $file) {
            if (is_dir($source['pathname'].$file)) {

                if (!self::copyDir($source['pathname'].$file, $target['pathname'].$file)) {
                    return false;
                }

            } elseif (!self::copy($source['pathname'].$file, $target['pathname'].$file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets a adapter.
     *
     * @param string $protocol
     */
    public static function getAdapter($protocol)
    {
        return isset(self::$adapters[$protocol]) ? self::$adapters[$protocol] : null;
    }

    /**
     * Registers a adapter.
     *
     * @param string           $protocol
     * @param AdapterInterface $adapter
     */
    public static function registerAdapter($protocol, AdapterInterface $adapter)
    {
        self::$adapters[$protocol] = $adapter;
    }
}
