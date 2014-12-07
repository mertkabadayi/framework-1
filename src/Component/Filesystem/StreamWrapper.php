<?php

namespace Pagekit\Component\Filesystem;

class StreamWrapper
{
    /**
     * @var resource
     */
    protected $handle;

    /**
     * Close directory handle.
     *
     * @return bool
     */
    public function dir_closedir()
    {
        closedir($this->handle);

        return true;
    }

    /**
     * Open directory handle.
     *
     * @param  string $path
     * @param  int    $options
     * @return bool
     */
    public function dir_opendir($path, $options)
    {
        $this->handle = opendir(File::getPath($path, true));

        return (bool) $this->handle;
    }

    /**
     * Read entry from directory handle.
     *
     * @return string
     */
    public function dir_readdir()
    {
        return readdir($this->handle);
    }

    /**
     * Rewind directory handle.
     *
     * @return bool
     */
    public function dir_rewinddir()
    {
        rewinddir($this->handle);

        return true;
    }

    /**
     * Create a directory.
     *
     * @param  string $path
     * @param  int    $mode
     * @param  int    $options
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {
        return mkdir(File::getPath($path, true), $mode, $options & STREAM_MKDIR_RECURSIVE);
    }

    /**
     * Renames a file or directory.
     *
     * @param  string $pathFrom
     * @param  string $pathTo
     * @return bool
     */
    public function rename($pathFrom, $pathTo)
    {
        return rename(File::getPath($pathFrom, true), File::getPath($pathTo, true));
    }

    /**
     * Removes a directory.
     *
     * @param  string $path
     * @param  int    $options
     * @return bool
     */
    public function rmdir($path, $options)
    {
        return rmdir(File::getPath($path, true));
    }

    /**
     * Delete a file.
     *
     * @param  $path string
     * @return bool
     */
    public function unlink($path)
    {
        return unlink(File::getPath($path, true));
    }

    /**
     * Retrieve information about a file.
     *
     * @param  string $path
     * @param  int    $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {
        $path = File::getPath($path, true);

        if ($flags & STREAM_URL_STAT_QUIET || !file_exists($path)) {
            return @stat($path);
        }

        return stat($path);
    }

    /**
     * Retrieve the underlaying resource.
     *
     * @param  int $castAs
     * @return resource
     */
    public function stream_cast($castAs)
    {
        return false;
    }

    /**
     * Close an resource.
     */
    public function stream_close()
    {
        fclose($this->handle);
    }

    /**
     * Tests for end-of-file on a file pointer.
     *
     * @return bool
     */
    public function stream_eof()
    {
        return feof($this->handle);
    }

    /**
     * Flushes the output.
     *
     * @return bool
     */
    public function stream_flush()
    {
        return fflush($this->handle);
    }

    /**
     * Advisory file locking.
     *
     * @param  int $operation
     * @return bool
     */
    public function stream_lock($operation)
    {
        if (in_array($operation, [LOCK_SH, LOCK_EX, LOCK_UN, LOCK_NB])) {
            return flock($this->handle, $operation);
        }

        return false;
    }

    /**
     * Opens file or URL.
     *
     * @param  string $path
     * @param  string $mode
     * @param  int    $options
     * @param  string $openedPath
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$openedPath)
    {
        $this->handle = fopen(File::getPath($path, true), $mode);

        return (bool) $this->handle;
    }

    /**
     * Read from stream.
     *
     * @param  int $count
     * @return bool
     */
    public function stream_read($count)
    {
        return fread($this->handle, $count);
    }

    /**
     * Seeks to specific location in a stream.
     *
     * @param  int $offset
     * @param  int $whence
     * @return bool
     */
    public function stream_seek($offset, $whence)
    {
        return !fseek($this->handle, $offset, $whence);
    }

    /**
     * Retrieve information about a file resource.
     *
     * @return array
     */
    public function stream_stat()
    {
        return fstat($this->handle);
    }

    /**
     * Retrieve the current position of a stream.
     *
     * @return int
     */
    public function stream_tell()
    {
        return ftell($this->handle);
    }

    /**
     * Write to stream.
     *
     * @param  string $data
     * @return int
     */
    public function stream_write($data)
    {
        return fwrite($this->handle, $data);
    }
}
