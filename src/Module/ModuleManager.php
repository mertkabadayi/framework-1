<?php

namespace Pagekit\Module;

use Pagekit\Application;

class ModuleManager implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * Get shortcut.
     *
     * @see get()
     */
    public function __invoke($name)
    {
        return $this->get($name);
    }

    /**
     * Gets a module.
     *
     * @param  string $name
     * @return ModuleInterface|null
     */
    public function get($name)
    {
        return isset($this->modules[$name]) ? $this->modules[$name] : null;
    }

    /**
     * Gets all modules.
     *
     * @return array
     */
    public function all()
    {
        return $this->modules;
    }

    /**
     * Loads all modules.
     *
     * @param Application $app
     */
    public function load(Application $app)
    {
        foreach ($this->loadConfigs() as $config) {

            $name = $config['name'];

            if (isset($this->modules[$name])) {
                continue;
            }

            if (isset($config['autoload'])) {
                foreach ($config['autoload'] as $namespace => $path) {
                    $app['autoloader']->addPsr4($namespace, $config['path']."/$path");
                }
            }

            if (is_string($class = $config['main'])) {

                $module = new $class;

                if ($module instanceof ModuleInterface) {
                    $module->load($app, $config);
                }

                $this->modules[$name] = $module;

            } elseif (is_callable($config['main'])) {

                $this->modules[$name] = call_user_func($config['main'], $app, $config) ?: true;
            }
        }
    }

    /**
     * Loads all module configs.
     *
     * @return array
     */
    public function loadConfigs()
    {
        $configs = [];
        $include = [];

        foreach ($this->paths as $path) {

            $paths = glob($path, GLOB_NOSORT) ?: [];

            foreach ($paths as $p) {

                if (!is_array($config = include($p)) && !isset($config['main'])) {
                    continue;
                }

                $config['path'] = strtr(dirname($p), '\\', '/');

                if (!isset($config['name'])) {
                    $config['name'] = basename($config['path']);
                }

                if (isset($config['include'])) {
                    $include = array_merge($include, (array) $config['include']);
                }

                $configs[] = $config;
            }
        }

        if ($this->paths = $include) {
            $configs = array_merge($configs, $this->loadConfigs());
        }

        return $configs;
    }

    /**
     * Adds a module path(s).
     *
     * @param  string|array $paths
     * @return self
     */
    public function addPath($paths)
    {
        $this->paths = array_merge($this->paths, (array) $paths);

        return $this;
    }

    /**
     * Checks if a module exists.
     *
     * @param  string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->modules[$name]);
    }

    /**
     * Gets a module by name.
     *
     * @param  string $name
     * @return bool
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Sets a module.
     *
     * @param string $name
     * @param string $module
     */
    public function offsetSet($name, $module)
    {
        $this->modules[$name] = $module;
    }

    /**
     * Unset a module.
     *
     * @param string $name
     */
    public function offsetUnset($name)
    {
        unset($this->modules[$name]);
    }
}
