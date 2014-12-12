<?php

namespace Pagekit\Component\Routing\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\RouteCollection;

class RoutesResolver extends ControllerResolver
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * Constructor.
     *
     * @param Routes $routes
     * @param LoggerInterface $logger
     */
    public function __construct(Routes $routes, LoggerInterface $logger = null)
    {
        parent::__construct($logger);

        $this->routes = $routes->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        $name = $request->attributes->get('_route', '');

        if ($route = $this->routes->get($name) and $callback = $route->getOption('__callback')) {
            return $callback;
        }

        return parent::getController($request);
    }
}
