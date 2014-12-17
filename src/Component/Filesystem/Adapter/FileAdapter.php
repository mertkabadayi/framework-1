<?php

namespace Pagekit\Component\Filesystem\Adapter;

class FileAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * Constructor.
     *
     * @param string $baseUrl;
     * @param string $basePath;
     */
    public function __construct($baseUrl = '', $basePath = '')
    {
        $this->baseUrl  = $baseUrl;
        $this->basePath = strtr($basePath, '\\', '/');
    }

    /**
     * {@inheritdoc}
     */
    public function getStreamWrapper()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathInfo(array $info)
    {
        $info['localpath'] = $info['pathname'];

        if ($info['root'] === '') {

            $root = $this->basePath;

            if (substr($root, -1) != '/') {
                $root .= '/';
            }

            $info['localpath'] = $root.$info['localpath'];
        }

        if ($this->baseUrl and $this->basePath and $info['localpath'] and file_exists($info['localpath'])) {
            if (strpos($info['localpath'], $this->basePath) === 0) {
                $info['url'] = $this->baseUrl.substr($info['localpath'], strlen($this->basePath));
            }
        }

        return $info;
    }
}
