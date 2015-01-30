<?php

namespace Pagekit\Module;

use Pagekit\Application;

interface ModuleInterface
{
    /**
     * Returns the module's name.
     */
    public function getName();

    /**
     * Returns the module's absolute path.
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
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * Loads the module.
     *
     * @param Application $app
     * @param array       $config
     */
    public function load(Application $app, array $config);
}
