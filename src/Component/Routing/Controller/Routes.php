<?php

namespace Pagekit\Component\Routing\Controller;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Routes implements ControllerCollectionInterface
{
    protected $routes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->routes = new RouteCollection;
    }

    /**
     * Maps a GET request to a callable.
     *
     * @param  string $path
     * @param  string $name
     * @param  mixed  $callback
     * @return Route
     */
    public function get($path, $name, $callback)
    {
        return $this->map($path, $name, $callback)->setMethods('GET');
    }

    /**
     * Maps a POST request to a callable.
     *
     * @param  string $path
     * @param  string $name
     * @param  mixed  $callback
     * @return Route
     */
    public function post($path, $name, $callback)
    {
        return $this->map($path, $name, $callback)->setMethods('POST');
    }

    /**
     * Maps a path to a callable.
     *
     * @param  string $path
     * @param  string $name
     * @param  mixed  $callback
     * @return Route
     */
    public function map($path, $name, $callback)
    {
        $route = (new Route($path))->setOption('__callback', $callback);

        $this->routes->add($name, $route);

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        $resources = [];

        foreach ($this->routes as $name => $route) {
            $resources[] = $name.$route->getPath();
        }

        return ['callbacks' => $resources];
    }
}
