<?php

namespace Pagekit\Module;

use Pagekit\Application;
use Pagekit\Module\Config\LoaderInterface;

class ModuleManager implements \ArrayAccess
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ModuleInterface[]
     */
    protected $modules = [];

    /**
     * @var array
     */
    protected $loaded = [];

    /**
     * @var array
     */
    protected $sorted = [];

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = [];

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
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
     * Get all loaded modules.
     *
     * @return ModuleInterface[]
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

        foreach ($this->sortedConfigs() as $config) {

            $name = $config['name'];

            if (isset($this->modules[$name]) || !preg_match("/^($pattern)(\.|\/|$)/", $name)) {
                continue;
            }

            foreach ($this->loaders as $loader) {
                $config = $loader->load($name, $config);
            }

            if (isset($config['autoload'])) {
                foreach ($config['autoload'] as $namespace => $path) {
                    $this->app['autoloader']->addPsr4($namespace, $config['path']."/$path");
                }
            }

            $class = is_string($config['main']) ? $config['main'] : 'Pagekit\\Module\\Module';

            $module = new $class($config);

            if (false !== $module->main($this->app)) {
                $this->modules[$name] = $module;
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

                if (!is_array($config = include $p) || !isset($config['name'])) {
                    continue;
                }

                $priority = 0;

                if (isset($config['priority'])) {
                    $priority = (int) $config['priority'];
                }

                if (isset($config['include'])) {
                    $include = array_merge($include, (array) $config['include']);
                }

                $config['path'] = strtr(dirname($p), '\\', '/');

                $this->loaded[$priority][] = $config;
            }
        }

        if ($this->paths = $include) {
            $this->loadConfigs();
        }

        return $this->loaded;
    }

    /**
     * Gets sorted module configs.
     *
     * @return array
     */
    public function sortedConfigs()
    {
        $configs = $this->loadConfigs();

        if (empty($this->sorted)) {
            krsort($configs);
            $this->sorted = call_user_func_array('array_merge', $configs);
        }

        return $this->sorted;
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
        $this->sorted = [];

        return $this;
    }

    /**
     * Adds a config loader.
     *
     * @param  LoaderInterface $loader
     * @return self
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;

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
