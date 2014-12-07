<?php

namespace Pagekit\Component\Filesystem\Adapter;

class PathAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param string $path;
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function parsePathInfo(array $info)
    {
        $info['pathname'] = $this->path.rtrim('/'.$info['path'], '/');

        return $info;
    }
}
