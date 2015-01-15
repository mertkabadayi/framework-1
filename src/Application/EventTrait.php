<?php

namespace Pagekit\Application;

use Pagekit\Application as App;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

trait EventTrait
{
    /**
     * @see EventDispatcherInterface::dispatch
     */
    public static function trigger($eventName, Event $event = null)
    {
        return App::events()->dispatch($eventName, $event);
    }

    /**
     * @see EventDispatcherInterface::addListener
     */
    public static function on($event, $callback, $priority = 0)
    {
        App::events()->addListener($event, $callback, $priority);
    }

    /**
     * @see EventDispatcherInterface::addSubscriber
     */
    public static function subscribe(EventSubscriberInterface $subscriber)
    {
        $subscribers = func_num_args() > 1 ? func_get_args() : [$subscriber];

        foreach ($subscribers as $sub) {
            App::events()->addSubscriber($sub);
        }
    }
}
