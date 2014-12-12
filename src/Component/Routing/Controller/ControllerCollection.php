<?php

namespace Pagekit\Component\Routing\Controller;

use Composer\Autoload\ClassLoader;
use Pagekit\Component\Routing\Exception\ControllerFrozenException;
use Pagekit\Component\Routing\Exception\LoaderException;
use Symfony\Component\Routing\RouteCollection;

class ControllerCollection implements ControllerCollectionInterface
{
    protected $reader;
    protected $loader;
    protected $prefix = '';
    protected $namespace = '';
    protected $defaults = [];
    protected $requirements = [];
    protected $controllers = [];
    protected $isFrozen = false;

    /**
     * Constructor.
     *
     * @param ControllerReaderInterface $reader
     * @param ClassLoader $loader
     */
    public function __construct(ControllerReaderInterface $reader, ClassLoader $loader)
    {
        $this->reader = $reader;
        $this->loader = $loader;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param  string $prefix
     * @return self
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param  string $namespace
     * @return self
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param  array $defaults
     * @return self
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @param  array $requirements
     * @return self
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
        return $this;
    }

    /**
     * Adds a controller.
     *
     * @param  string $controller
     * @return ControllerCollection
     */
    public function add($controller)
    {
        return $this->controllers[] = $controller;
    }

    /**
     * Adds controllers.
     *
     * @param  string|string[]|ControllerCollection $controllers
     * @return ControllerCollection
     */
    public function addCollection($controllers)
    {
        if (!$controllers instanceof ControllerCollectionInterface) {
            $collection = new ControllerCollection($this->reader, $this->loader);
            foreach ((array) $controllers as $controller) {
                $collection->add($controller);
            }
            $controllers = $collection;
        }

        return $this->controllers[] = $controllers;
    }

    /**
     * Mounts controllers under given prefix and namespace
     *
     * @param string $prefix
     * @param string|string[]|ControllerCollection $controllers
     * @param string $namespace
     * @param array  $defaults
     * @param array  $requirements
     */
    public function mount($prefix, $controllers, $namespace, array $defaults = [], array $requirements = [])
    {
        $this->addCollection($controllers)
            ->setPrefix($prefix)
            ->setNamespace($namespace)
            ->setDefaults($defaults)
            ->setRequirements($requirements);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if ($this->isFrozen) {
            throw new ControllerFrozenException(sprintf('Calling %s on frozen %s instance.', __METHOD__, __CLASS__));
        }

        $routes = new RouteCollection;
        foreach ($this->controllers as $controller) {

            try {

                foreach (is_string($controller) ? $this->reader->read($controller) : $controller->flush() as $name => $route) {
                    $routes->add($this->namespace.$name, $route);
                }

            } catch (LoaderException $e) {}

        }
        $routes->addPrefix($this->prefix, $this->defaults, $this->requirements);

        $this->isFrozen = true;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        $resources = [];
        foreach ($this->controllers as $controller) {
            if (is_string($controller)) {
                $resources['controllers'][] = $this->loader->findFile($controller);
            } else {
                $resources = array_merge_recursive($resources, $controller->getResources());
            }
        }

        return $resources;
    }
}
