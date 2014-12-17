<?php

namespace Pagekit\Component\Filesystem\Adapter;

class PathAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $wrapper;

    /**
     * Constructor.
     *
     * @param string $path;
     * @param string $wrapper;
     */
    public function __construct($path, $wrapper = null)
    {
        $this->path    = $path;
        $this->wrapper = $wrapper ?: 'Pagekit\Component\Filesystem\StreamWrapper';
    }

    /**
     * {@inheritdoc}
     */
    public function getStreamWrapper()
    {
        return $this->wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathInfo(array $info)
    {
        $info['localpath'] = $this->path.rtrim('/'.$info['path'], '/');

        return $info;
    }
}
