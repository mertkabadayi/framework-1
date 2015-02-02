<?php

namespace Pagekit\Module;

use Pagekit\Application;

interface ModuleInterface
{
    /**
     * Returns the module's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the module's absolute path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns the module's config.
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return array
     */
    public function getConfig($key = null, $default = null);

    /**
     * Sets the module's config.
     *
     * @param  array $config
     * @return self
     */
    public function setConfig(array $config);
}
