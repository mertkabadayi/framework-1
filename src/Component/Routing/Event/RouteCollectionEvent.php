<?php

namespace Pagekit\Component\Routing\Event;

use Pagekit\Component\Routing\Router;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\RouteCollection;

class RouteCollectionEvent extends Event
{
    protected $router;
    protected $routes;

    /**
     * Constructs an event.
     */
    public function __construct(Router $router, RouteCollection $routes)
    {
        $this->router = $router;
        $this->routes = $routes;
    }

    /**
     * Returns the router.
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Returns the routes collection.
     *
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
