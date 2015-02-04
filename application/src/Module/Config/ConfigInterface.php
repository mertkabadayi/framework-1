<?php

namespace Pagekit\Module\Config;

interface ConfigInterface
{
    /**
     * Get the module config.
     *
     * @param  string $name
     * @param  array  $config
     * @return array
     */
    public function get($name, array $config);

    /**
     * Import config values.
     *
     * @param array $values
     */
    public function import(array $values);
}
