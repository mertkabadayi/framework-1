<?php

namespace Pagekit\Module\Config;

class Config
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * {@inheritdoc}
     */
    public function get($name, array $config)
    {
        if (isset($this->values[$name])) {
            $config = array_replace_recursive($config, $this->values[$name]);
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $values)
    {
        $this->values = array_replace_recursive($this->values, $values);
    }
}
