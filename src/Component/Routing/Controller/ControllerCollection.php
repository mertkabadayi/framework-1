<?php

namespace Pagekit\Component\Routing\Controller;

use Composer\Autoload\ClassLoader;
use Pagekit\Component\Routing\Event\RouteCollectionEvent;
use Pagekit\Component\Routing\Event\RouteResourcesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;

class ControllerCollection implements EventSubscriberInterface
{
    protected $reader;
    protected $loader;
    protected $routes = [];

    /**
     * Constructor.
     *
     * @param ControllerReaderInterface $reader
     * @param ClassLoader               $loader
     */
    public function __construct(ControllerReaderInterface $reader, ClassLoader $loader)
    {
        $this->reader = $reader;
        $this->loader = $loader;
    }

    /**
     * Mounts controllers under given prefix and namespace
     *
     * @param string          $prefix
     * @param string|string[] $controllers
     * @param string          $namespace
     * @param array           $defaults
     * @param array           $requirements
     */
    public function mount($prefix, $controllers, $namespace, array $defaults = [], array $requirements = [])
    {
        $this->routes[] = new Route($prefix, $defaults, $requirements, compact('namespace', 'controllers'));
    }

    /**
     * Adds this instances routes to the collection.
     *
     * @param RouteCollectionEvent $event
     */
    public function getRoutes(RouteCollectionEvent $event)
    {
        $routes = $event->getRoutes();
        $loaded = [];
        foreach ($this->routes as $route) {
            foreach ((array) $route->getOption('controllers') as $controller) {

                if (!isset($loaded[$controller])) {

                    try {
                        $loaded[$controller] = $this->reader->read($controller);
                    } catch (\InvalidArgumentException $e) {
                        continue;
                    }

                }

                foreach (clone $loaded[$controller] as $name => $r) {

                    $i    = 2;
                    $name = $orig = $route->getOption('namespace').$name;
                    while ($routes->get($name)) {
                        $name = "{$orig}_{$i}";
                        $i++;
                    }

                    $routes->add($name, $r
                        ->setPath(rtrim($route->getPath().$r->getPath(), '/'))
                        ->addDefaults($route->getDefaults())
                        ->addRequirements($route->getRequirements()
                        ));
                }
            }
        }
    }

    /**
     * Adds this instances resources to the collection
     *
     * @param RouteResourcesEvent $event
     */
    public function getResources(RouteResourcesEvent $event)
    {
        $resources = [];
        foreach ($this->routes as $route) {
            foreach ((array) $route->getOption('controllers') as $controller) {
                if (is_string($controller) && $file = $this->loader->findFile($controller)) {
                    $resources[] = ['file' => $file, 'prefix' => $route->getPath()];
                }
            }
        }
        $event->addResources($resources);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'route.collection' => ['getRoutes', 8],
            'route.resources'  => 'getResources'
        ];
    }
}
