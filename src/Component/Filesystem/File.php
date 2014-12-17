<?php

namespace Pagekit\Component\Filesystem;

use Pagekit\Component\Filesystem\Adapter\AdapterInterface;

class File
{
    protected static $adapters = [];

    /**
     * Gets file path URL.
     *
     * @param  string $file
     * @param  mixed  $referenceType
     * @return string|false
     */
    public static function getUrl($file, $referenceType = false)
    {
        if (!$url = self::getPathInfo($file, 'url')) {
            return false;
        }

        if ($referenceType === false) {
            $url = strlen($path = parse_url($url, PHP_URL_PATH)) > 1 ? substr($url, strpos($url, $path)) : '/';
        } elseif ($referenceType === 'network') {
            $url = substr($url, strpos($url, '//'));
        }

        return $url;
    }

    /**
     * Gets canonicalized file path or localpath.
     *
     * @param  string $file
     * @param  bool   $local
     * @return string|false
     */
    public static function getPath($file, $local = false)
    {
        return self::getPathInfo($file, $local ? 'localpath' : 'pathname') ?: false;
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
            $info = $adapter->getPathInfo($info);
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
        $source = self::getPathInfo($source, 'pathname');
        $target = self::getPathInfo($target);

        if (!is_file($source) || !self::makeDir($target['dirname'])) {
            return false;
        }

        return @copy($source, $target['pathname']);
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

            $file = self::getPathInfo($file, 'pathname');

            if (is_dir($file)) {

                if (substr($file, -1) != '/') {
                    $file .= '/';
                }

                foreach (self::listDir($file) as $name) {
                    if (!self::delete($file.$name)) {
                        return false;
                    }
                }

                if (!@rmdir($file)) {
                    return false;
                }

            } elseif (!@unlink($file)) {
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
        $source = self::getPathInfo($source, 'pathname');
        $target = self::getPathInfo($target, 'pathname');

        if (!is_dir($source) || !self::makeDir($target)) {
            return false;
        }

        if (substr($source, -1) != '/') {
            $source .= '/';
        }

        if (substr($target, -1) != '/') {
            $target .= '/';
        }

        foreach (self::listDir($source) as $file) {
            if (is_dir($source.$file)) {

                if (!self::copyDir($source.$file, $target.$file)) {
                    return false;
                }

            } elseif (!self::copy($source.$file, $target.$file)) {
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

        if ($wrapper = $adapter->getStreamWrapper()) {
            stream_wrapper_register($protocol, $wrapper);
        }
    }
}
