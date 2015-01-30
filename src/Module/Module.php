<?php

namespace Pagekit\Module;

use Pagekit\Application;

abstract class Module implements ModuleInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->config['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->config['path'];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($key = null, $default = null)
    {
        if (null === $key) {
            return $this->config;
        }

        $array = $this->config;

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {

            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}
