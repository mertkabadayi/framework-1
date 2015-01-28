<?php

namespace Pagekit\Module;

use Pagekit\Application;

interface ModuleInterface
{
    /**
     * Loads the module.
     *
     * @param Application $app
     * @param array       $config
     */
    public function load(Application $app, array $config);
}
