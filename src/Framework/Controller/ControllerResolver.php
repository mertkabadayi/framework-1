<?php

namespace Pagekit\Framework\Controller;

use Pagekit\Component\Routing\Controller\RoutesResolver;
use Pagekit\Framework\ApplicationTrait;

class ControllerResolver extends RoutesResolver implements \ArrayAccess
{
    use ApplicationTrait;

    /**
     * @{inheritdoc}
     */
    protected function createController($controller)
    {
        if (strpos($controller, '::') === false) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $reflection = new \ReflectionClass($class);

        if ($constructor = $reflection->getConstructor()) {

            $args = [];

            foreach ($constructor->getParameters() as $param) {
                if ($class = $param->getClass()) {

                    if ($class->isInstance(self::$app)) {
                        $args[] = self::$app;
                    } elseif ($extension = $this['extensions']->get($class->getName())) {
                        $args[] = $extension;
                    }

                } else {
                    throw new \InvalidArgumentException(sprintf('Unknown constructor argument "$%s".', $param->getName()));
                }
            }

            $instance = $reflection->newInstanceArgs($args);
        }

        return [isset($instance) ? $instance : new $class, $method];
    }
}
