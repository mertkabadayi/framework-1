<?php

namespace Pagekit\Module\Config;

class ArrayLoader implements LoaderInterface
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * Constructor.
     *
     * @param array $values
     */
    public function __construct($values) {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function load($name, array $config)
    {
        if (isset($this->values[$name])) {
            $config = array_replace_recursive($config, ['config' => $this->values[$name]]);
        }

        return $config;
    }
}
