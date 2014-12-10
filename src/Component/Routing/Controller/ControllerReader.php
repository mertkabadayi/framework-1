<?php

namespace Pagekit\Component\Routing\Controller;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Pagekit\Component\Routing\Annotation\Route as RouteAnnotation;
use Pagekit\Component\Routing\Event\ConfigureRouteEvent;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ControllerReader implements ControllerReaderInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $events;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var int
     */
    protected $routeIndex;

    /**
     * @var string
     */
    protected $routeAnnotation = 'Pagekit\Component\Routing\Annotation\Route';

    /**
     * @var \ReflectionClass
     */
    protected $route;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $events
     * @param Reader                   $reader
     */
    public function __construct(EventDispatcherInterface $events, Reader $reader = null)
    {
        $this->events = $events;
        $this->reader = $reader;
        $this->route  = new ReflectionClass('Symfony\Component\Routing\Route');
    }

    /**
     * {@inheritdoc}
     */
    public function read(ReflectionClass $class, array $options = [])
    {
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class));
        }

        $options = array_replace([
            'path'         => null,
            'defaults'     => [],
            'requirements' => [],
            'options'      => [],
            'host'         => '',
            'schemes'      => [],
            'methods'      => [],
            'condition'    => '',
            'name'         => null
        ], $options);

        if ($annotation = $this->getAnnotationReader()->getClassAnnotation($class, $this->routeAnnotation)) {
            foreach(array_keys($options) as $option) {
                $method = 'get'.ucfirst($option);
                if (null !== $value = $annotation->$method()) {
                    $options[$option] = $value;
                }
            }
        }

        if ($options['path'] === null) {
            $options['path'] = strtolower($this->parseControllerName($class));
        }

        if ($options['name'] === null) {
            $options['name'] = '@'.strtolower($this->parseControllerName($class));
        }

        $this->routes = new RouteCollection;

        foreach ($class->getMethods() as $method) {

            $this->routeIndex = 0;

            if ($method->isPublic() && 'Action' == substr($method->name, -6)) {

                $count = $this->routes->count();

                foreach ($this->getAnnotationReader()->getMethodAnnotations($method) as $annotation) {
                    if ($annotation instanceof $this->routeAnnotation) {
                        $this->addRoute($class, $method, $options, $annotation);
                    }
                }

                if ($count == $this->routes->count()) {
                    $this->addRoute($class, $method, $options);
                }
            }
        }

        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ReflectionClass $class)
    {
        return true;
    }

    /**
     * Get the annotation reader instance.
     *
     * @return Reader
     */
    protected function getAnnotationReader()
    {
        if (!$this->reader) {
            $this->reader = new SimpleAnnotationReader;
            $this->reader->addNamespace('Pagekit\Component\Routing\Annotation');
        }

        return $this->reader;
    }

    /**
     * Creates a new route.
     *
     * @param ReflectionClass  $class
     * @param ReflectionMethod $method
     * @param array            $options
     * @param RouteAnnotation  $annotation
     */
    protected function addRoute(ReflectionClass $class, ReflectionMethod $method, array $options, $annotation = null)
    {
        $path = $options['path'];
        $options['name'] = $this->getDefaultRouteName($class, $method, $options);
        $options['path'] = $this->getDefaultRoutePath($class, $method, $options);

        if ($annotation) {
            $options['path']         = null !== $annotation->getPath() ? $annotation->getPath() : $options['path'];
            $options['defaults']     = array_merge($options['defaults'], $annotation->getDefaults());
            $options['requirements'] = array_merge($options['requirements'], $annotation->getRequirements());
            $options['options']      = array_merge($options['options'], $annotation->getOptions());
            $options['host']         = null !== $annotation->getHost() ? $annotation->getHost() : $options['host'];
            $options['schemes']      = array_merge($options['schemes'], $annotation->getSchemes());
            $options['methods']      = array_merge($options['methods'], $annotation->getMethods());
            $options['condition']    = null !== $annotation->getCondition() ? $annotation->getCondition() : $options['condition'];
            $options['name']         = null !== $annotation->getName() ? $annotation->getName() : $options['name'];
        }

        $options['path'] = rtrim($path.$options['path'], '/');

        if ($route = $this->configureRoute($this->route->newInstanceArgs($options), $class, $method, $options)) {
            $this->routes->add($options['name'], $route);
        }
    }

    /**
     * Configure the route, should be overridden in subclasses.
     *
     * @param  Route            $route
     * @param  ReflectionClass  $class
     * @param  ReflectionMethod $method
     * @param  array            $options
     * @return Route|null
     */
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, array $options)
    {
        $route->setDefault('_controller', $class->name.'::'.$method->name);
        return $this->events->dispatch('route.configure', new ConfigureRouteEvent($route, $class, $method, $options))->getRoute();
    }

    /**
     * Gets the default route path for a class method.
     *
     * @param  ReflectionClass  $class
     * @param  ReflectionMethod $method
     * @param  array            $options
     * @return string
     */
    protected function getDefaultRoutePath(ReflectionClass $class, ReflectionMethod $method, array $options)
    {
        $action = strtolower('/'.$this->parseControllerActionName($method));

        if ($action == '/index') {
            $action = '';
        }

        return $action;
    }

    /**
     * Gets the default route name for a class method.
     *
     * @param  ReflectionClass  $class
     * @param  ReflectionMethod $method
     * @param  array            $options
     * @return string
     */
    protected function getDefaultRouteName(ReflectionClass $class, ReflectionMethod $method, array $options)
    {
        $action = strtolower('/'.$this->parseControllerActionName($method));

        if ($action == '/index') {
            $action = '';
        }

        $name = $options['name'].$action;

        if ($this->routeIndex > 0) {
            $name .= '_'.$this->routeIndex;
        }

        $this->routeIndex++;

        return $name;
    }

    /**
     * Parses the controller name.
     *
     * @param  ReflectionClass $class
     * @throws \LogicException
     * @return string
     */
    protected function parseControllerName(ReflectionClass $class)
    {
        if (!preg_match('/([a-zA-Z0-9]+)Controller$/', $class->name, $matches)) {
            throw new \LogicException(sprintf('Unable to retrieve controller name. The controller class %s does not follow the naming convention. (e.g. MyController)', $class->name));
        }

        return $matches[1];
    }

    /**
     * Parses the controller action name.
     *
     * @param  ReflectionMethod $method
     * @throws \LogicException
     * @return string
     */
    protected function parseControllerActionName(ReflectionMethod $method)
    {
        if (!preg_match('/([a-zA-Z0-9]+)Action$/', $method->name, $matches)) {
            throw new \LogicException(sprintf('Unable to retrieve action name. The controller class method %s does not follow the naming convention. (e.g. indexAction)', $method->name));
        }

        return $matches[1];
    }
}
