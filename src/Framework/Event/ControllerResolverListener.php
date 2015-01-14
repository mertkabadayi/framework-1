<?php

namespace Pagekit\Framework\Event;

use Pagekit\Application as App;
use Pagekit\Routing\Event\GetControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ControllerResolverListener implements EventSubscriberInterface
{
    /**
     * Sets the Controller instance associated with a Request.
     *
     * @param GetControllerEvent $event
     */
    public function getController(GetControllerEvent $event)
    {
        if (!$controller = $event->getRequest()->attributes->get('_controller')) {
            return;
        }

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

                    if ($class->isInstance(App::getInstance())) {
                        $args[] = App::getInstance();
                    } elseif ($extension = $this['extensions']->get($class->getName())) {
                        $args[] = $extension;
                    }

                } else {
                    throw new \InvalidArgumentException(sprintf('Unknown constructor argument "$%s".', $param->getName()));
                }
            }

            $instance = $reflection->newInstanceArgs($args);
        }

        $event->setController([isset($instance) ? $instance : new $class, $method]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'controller.resolve' => 'getController'
        ];
    }
}
