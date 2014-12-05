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
    public function __construct($baseUrl = null, $basePath = null)
    {
        $this->baseUrl  = $baseUrl;
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function parsePathInfo(array $info)
    {
        if ($this->baseUrl and $this->basePath and $info['pathname'] and file_exists($info['pathname'])) {
            if (strpos($info['pathname'], $this->basePath) === 0) {
                $info['url'] = $this->baseUrl.substr($info['pathname'], strlen($this->basePath));
            }
        }

        return $info;
    }
}
