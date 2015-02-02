<?php

namespace Pagekit\Module;

use Pagekit\Application;

class ModuleManager implements \ArrayAccess
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var array
     */
    protected $loaded = [];

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app, array $config = [])
    {
        $this->app = $app;
        $this->config = $config;
    }

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
     * Loads the given modules.
     *
     * @param string|array $modules
     */
    public function load($modules)
    {
        $pattern = implode('|', (array) $modules);
        $pattern = str_replace('\|', '|', preg_quote($pattern, '/'));

        foreach ($this->loadConfigs() as $config) {

            $name = $config['name'];

            if (isset($this->loaded[$name]) || !preg_match("/^($pattern)(\.|\/|$)/", $name)) {
                continue;
            }

            if (isset($this->config[$name])) {
                $config = array_replace_recursive($config, $this->config[$name]);
            }

            if (isset($config['autoload'])) {
                foreach ($config['autoload'] as $namespace => $path) {
                    $this->app['autoloader']->addPsr4($namespace, $config['path']."/$path");
                }
            }

            $this->loaded[$name] = true;

            if (is_callable($config['main'])) {

                $module = call_user_func($config['main'], $this->app, $config);

                if ($module instanceof ModuleInterface) {
                    $this->modules[$name] = $module->setConfig($config);
                }
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
        $include = [];

        foreach ($this->paths as $path) {

            $paths = glob($path, GLOB_NOSORT) ?: [];

            foreach ($paths as $p) {

                if (!is_array($config = include($p)) || !isset($config['name'])) {
                    continue;
                }

                $config['path'] = strtr(dirname($p), '\\', '/');

                if (isset($config['include'])) {
                    $include = array_merge($include, (array) $config['include']);
                }

                $this->configs[] = $config;
            }
        }

        if ($this->paths = $include) {
            $this->loadConfigs();
        }

        return $this->configs;
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
